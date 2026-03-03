<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\DecisionsExport;
use App\Imports\DecisionsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DecisionController extends Controller
{
    /**
     * Экспортировать все разделы решений в один ZIP архив
     */
    public function exportAll()
    {
        $exporter = new DecisionsExport();
        return $exporter->exportAllToZip();
    }

    /**
     * Импортировать все разделы решений из одного ZIP архива
     */
    public function importAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip|max:204800', // 200MB для полного архива
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $importer = new DecisionsImport();
        $result = $importer->importAllFromZip($request->file('file'));

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}

