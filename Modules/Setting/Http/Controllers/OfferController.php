<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Display a listing of the offer setting.
     * @return Renderable
     */
    public function index()
    {
        if (checkRole('setting', 'view') == true) {
            activityLog('Admin', 'Opened Offer Settings Menu');
            return view('setting::offer.index');
        } else {
            return abort(404);
        }
    }

    /**
     * Update the specified offer setting in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        if (checkRole('setting', 'edit') == true) {
            activityLog('Admin', 'Offer Settings Updated');
            $data = $request->all();
            unset($data['_token']);
            foreach (['offer_signature', 'offer_college_logo'] as $key) {
                if ($request->hasFile($key)) checkUploadFile($request->file($key), 'images', $key);
            }
            foreach ($data as $key => $value) {
                if ($value != NULL) {
                    if ($key == 'offer_signature' || $key == 'offer_college_logo') {
                        $value = uploadFile(request()->$key, 'images/' . $key, 'images', $key);
                    }
                    updateSettingValue($key, $value);
                }
            }
            return redirect()->back()->with('success', 'Offer Settings updated successfully');
        } else {
            return redirect()->back()->with('failure', 'This user does not have permission to update settings');
        }
    }
}
