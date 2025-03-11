<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\CategoryRequest;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\Product\CategoryInterface;
use App\Repositories\Interfaces\Admin\Product\CategoryLanguageInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    protected $category;
    protected $catLang;
    protected $languages;

    public function __construct(CategoryInterface $category, CategoryLanguageInterface $catLang, LanguageInterface $languages)
    {
        $this->category = $category;
        $this->catLang = $catLang;
        $this->languages = $languages;
    }

    public function index(Request $request)
    {
        try {
            $data = [
                'categories'    => $this->category->paginate(get_pagination('index_form_paginate'), $request),
                'old_category'  => $this->category->get(old('category')),
            ];

            return view('admin.products.categories.index', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function store(CategoryRequest $request)
    {
        if (config('app.demo_mode')):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        $validated = $request->validate([
            'title' => 'required|unique:category_languages',
        ]);

        DB::beginTransaction();
        try {
            $position_up = $this->category->store($request);
            if ($position_up === 'position_up'):
                Toastr::error(__('You cannot add anymore child of this category'));
                return back()->withInput();
            endif;
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect()->route('categories');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function edit($id, Request $request)
    {
        try {
            $languages = $this->languages->all()->orderBy('id', 'asc')->get();

            $lang = $request->lang != '' ? $request->lang : \App::getLocale();
            $category_language = $this->category->getByLang($id, $lang);
            $category = $category_language->category;
            $data = [
                'category_language' => $category_language,
                'all_categories' => $this->category->getCategories($id),
                'languages' => $languages,
                'parent_category'  => $this->category->get(old('category',$category->parent_id)),
                'category' => $category,
                'lang' => $lang,
                'r' => $request->r ? $request->r : $request->server('HTTP_REFERER')
            ];
            if (addon_is_activated('ramdhani')) {
                $data['ranges'] = $category->ranges;
            }
            return view('admin.products.categories.update', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function update(CategoryRequest $request)
    {
        if (config('app.demo_mode')):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {
            $position_up = $this->category->update($request);
            if ($position_up === 'position_up'):
                Toastr::error(__('You cannot add anymore child of this category'));
                return back()->withInput();
            endif;
            Toastr::success(__('Updated Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function statusChange(Request $request)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $this->category->statusChange($request['data']);
            $response['message'] = __('Updated Successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function featureChange(Request $request)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $this->category->featureChange($request['data']);
            $response['message'] = __('Updated Successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function ajaxCategoryFilter(Request $request)
    {
        $term = trim($request->q);
        if (empty($term)) {
            return \Response::json([]);
        }

        $categories = $this->category->ajaxCategoryFilter($term);
        $formatted_category = [];


        foreach ($categories as $category) {
            if ($category->position == 2):
                $position = '¦--¦--';
            elseif ($category->position == 1):
                $position = '¦--';
            else:
                $position = '';
            endif;
            $formatted_category[] = ['id' => $category->id, 'text' => $position . $category->getTranslation('title', \App::getLocale())];
        }

        return \Response::json($formatted_category);
    }
}
