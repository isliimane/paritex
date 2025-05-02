<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Refund;
use App\Repositories\Interfaces\Admin\CommonInterface;
use App\Traits\PaymentTrait;
use App\Traits\pdfTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class CommonController extends Controller
{
    use PaymentTrait,PdfTrait;
    private $common;

    public function __construct(CommonInterface $common)
    {
        $this->common = $common;
    }

    public function delete($id)
    {
        if (config('app.demo_mode')):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        $urlArray       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments       = explode('/', $urlArray);
        $numSegments    = count($segments);
        $currentSegment = $segments[$numSegments - 2];
        if ($status = $this->common->delete($currentSegment, $id)):
            if ($status === 'used'):
                $response['message'] = __('Unable to delete because this type is already used');
                $response['status']  = 'error';
                $response['title']   = __('Ops..!');
            else:
                $response['message'] = __('Deleted Successfully!');
                $response['status']  = 'success';
                $response['title']   = __('Deleted');
            endif;
        else:
            $response['message'] = __('Something went wrong, please try again');
            $response['status']  = 'error';
            $response['title']   = __('Ops..!');
        endif;
        return response()->json($response);
    }

    public function statusChange(Request $request)
    {
        if (config('app.demo_mode')):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->common->statusChange($request['data']);
            $response['message'] = __('Updated Successfully');
            $response['title']   = __('Success');
            $response['status']   = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }

    }
    public function editInfo($page_name, $param1 = null)
    {
        $otherLinks = null;
        if ($param1) :
            $otherLinks = explode('/', $param1);
        endif;

        $refund = '';
        $payment_details = '';
        if ($page_name == 'refund-view') {
            $refund       = Refund::find($otherLinks[0]);
        }

        $data = [
            'payment_details'   => $payment_details,
            'otherLinks'        => $otherLinks,
            'refund'            => $refund,
        ];

        return view("admin.modals.$page_name", $data);
    }

    public function importSample(Request $request, $type)
    {
        $filename = 'excel/'.$type.'-import-sample.xlsx';
        if (file_exists(public_path($filename))):
            $filepath = public_path($filename);
            return response()->download($filepath);
        else:
            return back()->with('danger',__('file_not_found'));
        endif;
    }

    public function download(Request $request, $type)
    {
        try {
            if ($type == 'category'):
                $items  = Category::where('status',1)->get();
            elseif ($type == 'brand'):
                $items  = Brand::where('status',1)->get();
            else:
                return back()->with('danger',__('file_not_found'));
            endif;
            $font_name = $this->commonSetting();
            $pdf    = PDF::loadView('admin.common.category-brand', [
                'items' => $items,
                'title' => __($type),
                'font_name' => $font_name,
            ]);

            return $pdf->download($type . '.pdf');
        } catch (\Exception $e) {
            return back()->with('danger',($e->getMessage()));
        }
    }

    public function productImportPost(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required',
        ]);
        $extension = request()->file('file')->getClientOriginalExtension();

        if ($extension != 'xlsx' && $extension != 'xls' && $extension != 'csv'):
            return back()->with('danger', __('file_type_not_supported'));
        endif;

        $file = request()->file('file')->store('import');
        $import = new ProductImport();
        $import->import($file);

        unlink(storage_path('app/'.$file));
        Toastr::success(__('successfully_imported'));
        return back();
    }
}
