<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\Document;

class DocumentController extends Controller
{
    
    public function upload(Request $request) {
        
        $request->validate([
            'file' => 'required|max:2048',
        ]);
        
        $file = $request->file('file');

        $path = $file->getRealPath();
        $size = $file->getSize();
        $data = file_get_contents($path);
        $base64 = base64_encode(addslashes($data));

        $documento = new Document();
            
        $documento->user_id = Auth::user()->id;
        //$documento->documentable_type
        //$documento->documentable_id
        $documento->name    = $file->getClientOriginalName();
        $documento->size    = $size;
        $documento->mime    = mime_content_type($path);
        $documento->content = $base64;
        //$documento->description
                
        $documento->save();

        return response(['id' => $documento->id], 201);
    }
    
    public function privateDownload(string $id) {
        
        /*
         * ACL: solo l'owner può scaricare
         */
        $documento = Document::where(['id' => $id, 'user_id' => Auth::user()->id])->first();
        
        if(is_null($documento)) {
            return response('Documento non trovato', 200);
        }
        
        $nomeFile       = $documento->name;
        $mime           = $documento->mime;
        $base64_content = $documento->getRawOriginal('content'); // SKIP ACCESSOR
        $size           = $documento->size;
        $content = stripslashes(base64_decode($base64_content));

        return response($content)
                        ->withHeaders([
                            'Content-Transfer-Encoding' => 'binary',
                            'Content-type' => $mime,
                            'Content-Disposition' => sprintf('attachment; filename="%s"', $nomeFile),
                            'Content-Length' => $size,
                            'Accept-Ranges' => 'bytes',
        ]);
    }
    
    public function publicDownload(string $id) {
        
        /*
         * ACL: tutti possono scaricare i docs di tutti
         */
        $documento = Document::where(['id' => $id, 'public' => true])->first();
        
        if(is_null($documento)) {
            return response('Documento non trovato', 200);
        }
        
        $nomeFile       = $documento->name;
        $mime           = $documento->mime;
        $base64_content = $documento->getRawOriginal('content'); // SKIP ACCESSOR
        $size           = $documento->size;
        $content = stripslashes(base64_decode($base64_content));

        return response($content)
                        ->withHeaders([
                            'Content-Transfer-Encoding' => 'binary',
                            'Content-type' => $mime,
                            'Content-Disposition' => sprintf('attachment; filename="%s"', $nomeFile),
                            'Content-Length' => $size,
                            'Accept-Ranges' => 'bytes',
        ]);
    }
    
//    public function publicDownloadThumbnail(string $id) {
//        
//        /*
//         * ACL: tutti possono scaricare i docs di tutti
//         */
//        $documento = Document::where(['id' => $id])->first();
//        
//        $nomeFile       = $documento->name;
//        $mime           = $documento->mime;
//        $base64_content = $documento->getRawOriginal('content'); // SKIP ACCESSOR
//        $size           = $documento->size;
//        $content = stripslashes(base64_decode($base64_content));
//
//        $content = Image::fromString($content)->resize(50);
//        
//        return response($content)
//                        ->withHeaders([
//                            'Content-Transfer-Encoding' => 'binary',
//                            'Content-type' => $mime,
//                            'Content-Disposition' => sprintf('attachment; filename="%s"', $nomeFile),
//                            'Content-Length' => $size,
//                            'Accept-Ranges' => 'bytes',
//        ]);
//    }
    
    public function remove(Request $request, string $id) {
        
        /*
         * Solo l'owner può cancellare!
         * Non viene fatto alcun controllo:
         * 1 - parte un garbage collector per i files privi di morph
         * 2 - se un file non viene trovato non esiste già
         */
        $doc             = Document::where(['id' => $id, 'user_id' => Auth::user()->id])->first();
        $documentable_id = $doc->documentable_id;
        $doc->forceDelete();   
        
        return response(['id' => $id, 'entity_id' => $documentable_id], 200);
    }
    
}
