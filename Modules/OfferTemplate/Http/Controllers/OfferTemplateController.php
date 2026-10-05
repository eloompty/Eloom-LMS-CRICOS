<?php

namespace Modules\OfferTemplate\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OfferTemplate\Entities\OfferTemplate;
use Illuminate\Support\Facades\File;

class OfferTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (checkRole('template', 'view') == true) {
            $templates = OfferTemplate::orderBy('id', 'desc')->get();
            return view('offertemplate::index', compact('templates'))->with('no', 1);
        } else {
            return abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        activityLog('Admin', 'Opened Create Offer template Page');
        return view('offertemplate::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('offertemplate::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $template = OfferTemplate::findorfail($id);
        if ($template) {
            $filePath = public_path($template->path);
            if (!File::exists($filePath)) {
                abort(404);
            }
            $htmlContent = file_get_contents($filePath);
            $templateID = $template->id;
            return view('offertemplate::edit', compact('template', 'templateID', 'htmlContent'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'htmlContent' => 'required|string',
            'name' => 'required|string'
        ]);

        // $template = CertificateTemplate::findorfail($id);

        $htmlContent = $request->input('htmlContent');
        $fileName = templateFileName($request->input('name'));
        $pathToSave = "templates/certificate/" . $fileName . ".html";
        $filePath = public_path($pathToSave);
        // Ensure the directory exists
        if (!File::exists(public_path('templates/certificate/'))) {
            File::makeDirectory(public_path('templates/certificate/'), 0755, true, true);
        }

        // Save the HTML file
        if (file_put_contents($filePath, $htmlContent)) {
            $template =  OfferTemplate::where('id', $id)->update([
                'name' => $request->name,
                'path' => $pathToSave
            ]);
            return response()->json(['success' => true, 'message' => 'HTML updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update HTML']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function loadHTML($id, $request)
    {
        if (checkRole('template', 'create') == true) {
            $fileName = templateFileName($request->name);
            $pathToFind = "templates/offer" . $fileName . ".html";
            $filePath = public_path($pathToFind);
            if (!File::exists($filePath)) {
                return view('template::html.upload', compact('template'));
            }
            $htmlContent = file_get_contents($filePath);
            return view('template::html.upload', compact('template', 'htmlContent'));
        } else {
            return abort(404);
        }
    }

    public function convertDoc(Request $request)
    {
        $request->validate([
            'docxFile' => 'required|mimes:docx|max:4048'
        ]);

        $file = $request->file('docxFile');
        $result = convertDocxToHtml($file);

        return response()->json($result);
    }

    public function saveHtml(Request $request)
    {
        $request->validate([
            'htmlContent' => 'required|string',
            'name' => 'required|string',
        ]);

        $htmlContent = $request->input('htmlContent');
        $fileName = templateFileName($request->input('name'));
        $fileNameToSave = $fileName . ".html";
        $pathToSave = "templates/offer/" . $fileName . ".html";
        $filePath = public_path($pathToSave);
        // Ensure the directory exists
        if (!File::exists(public_path('templates/offer/'))) {
            File::makeDirectory(public_path('templates/offer/'), 0755, true, true);
        }

        // Save the HTML file
        if (file_put_contents($filePath, $htmlContent)) {
            $template = OfferTemplate::create([
                'name' => $request->name,
                'path' => $pathToSave
            ]);
            return response()->json(['success' => true, 'message' => 'HTML saved successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to save HTML']);
        }
    }
}
