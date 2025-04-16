<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HackAssembler;
use App\Http\Requests\HackCodeRequest;

class AseemblerController extends Controller
{
    public function translate(HackCodeRequest $request, HackAssembler $assembler)
    {
       
        $hackCode = $request->validated()['hackCode'];

        try {
          
            $binaryCode = $assembler->assemble($hackCode);

            return back()->withInput()->with('binary', $binaryCode);

        } catch (\Exception $e) {
            //error during assembly
            return back()->withInput()->withErrors([
                'hackCode' => 'Assembly failed: ' . $e->getMessage()
            ]);
        }
    }
}
