<?php namespace App\Http\Controllers\Api;

use Auth;
use Fractal;
use Validator;
use App\Bookmark;
use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transformers\CategoryTransformer;

class CategoryController extends Controller
{

    public function all()
    {
        $categories = Auth::user()->categories;

        $array = Fractal::collection($categories, new CategoryTransformer())->getArray();

        return $this->apiResponse(0, '', $array);
    }

    public function create(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return $this->apiResponse(1, $validator->errors()->first());
        }

        /** 创建分类 */
        $category = new Category;
        $category->user_id       = Auth::id();
        $category->category_name = $request->input('category_name');
        $category->save();

        return $this->apiResponse(0, '添加成功！');
    }

    public function edit(Request $request, $category_id)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return $this->apiResponse(1, $validator->errors()->first());
        }

        $category = Auth::user()->categories()->where('id', $category_id)->first();

        if (is_null($category)) {
            return $this->apiResponse(0, '分类不存在！');
        }

        $category->category_name = $request->input('category_name');
        $category->save();

        return $this->apiResponse(0, '操作成功！');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'category_name' => 'required|max:10',
        ], [
            'category_name.required' => '请填写分类名',
            'category_name.max'      => '分类长度最大10个字',
        ]);
    }

    public function delete($category_id)
    {
        $isExists = Auth::user()->categories()->where('id', $category_id)->first();
        if (is_null($isExists)) {
            return $this->apiResponse(1, '分类不存在！');
        }
        $category = $isExists;

        /** 删除该分类下的所有书签 */
        Bookmark::where('category_id', $category->id)->delete();

        /** 删除分类 */
        Category::where('id', $category->id)->delete();

        return $this->apiResponse(0, '操作成功！');
    }

    public function find($id)
    {
        $category = Auth::user()->categories()->where('id', $id)->first();

        if (is_null($category)) {
            return $this->apiResponse(1, '分类不存在');
        }

        return $this->apiResponse(0, '', Fractal::item($category, new CategoryTransformer())->getArray());
    }
}
