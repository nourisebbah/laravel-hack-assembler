<?php

namespace App\Services;

class HackAssembler
{
    protected array $symbolTable = [];
    protected int $nextAddress = 16;

    private array $predefined = [
        'SP' => 0,
        'LCL' => 1,
        'ARG' => 2,
        'THIS' => 3,
        'THAT' => 4,
        'R0' => 0,
        'R1' => 1,
        'R2' => 2,
        'R3' => 3,
        'R4' => 4,
        'R5' => 5,
        'R6' => 6,
        'R7' => 7,
        'R8' => 8,
        'R9' => 9,
        'R10' => 10,
        'R11' => 11,
        'R12' => 12,
        'R13' => 13,
        'R14' => 14,
        'R15' => 15,
        'SCREEN' => 16384,
        'KBD' => 24576,
    ];

    private array $comp = [
        '0' => '0101010',
        '1' => '0111111',
        '-1' => '0111010',
        'D' => '0001100',
        'A' => '0110000',
        '!D' => '0001101',
        '!A' => '0110001',
        '-D' => '0001111',
        '-A' => '0110011',
        'D+1' => '0011111',
        'A+1' => '0110111',
        'D-1' => '0001110',
        'A-1' => '0110010',
        'D+A' => '0000010',
        'D-A' => '0010011',
        'A-D' => '0000111',
        'D&A' => '0000000',
        'D|A' => '0010101',
        'M' => '1110000',
        '!M' => '1110001',
        '-M' => '1110011',
        'M+1' => '1110111',
        'D+M' => '1000010',
        'D-M' => '1010011',
        'M-D' => '1000111',
        'D&M' => '1000000',
        'D|M' => '1010101',
    ];

    private array $dest = [
        '' => '000',
        'M' => '001',
        'D' => '010',
        'MD' => '011',
        'A' => '100',
        'AM' => '101',
        'AD' => '110',
        'AMD' => '111',
    ];

    private array $jump= [
        '' => '000',
        'JGT' => '001',
        'JEQ' => '010',
        'JGE' => '011',
        'JLT' => '100',
        'JNE' => '101',
        'JLE' => '110',
        'JMP' => '111',
    ];

    public function __construct()
    {
        // merge predefined symbols into symbol table
        $this->symbolTable = $this->predefined;
    }

    public function assemble(string $code): string
    {
        $lines = explode("\n", $code);
        $cleaned = $this->cleanLines($lines);
        $this->register($cleaned);
        return $this->generate($cleaned);
    }

    private function cleanLines(array $lines): array
    {
        return array_values(array_filter(array_map(function ($line) {
            $line = trim($line);
            return ($line === '' || str_starts_with($line, '//')) ? null : explode('//', $line)[0];
        }, $lines)));
    }

    private function register(array $lines): void
    {
        //to process labels and store them in the symbol table
        $romAddress = 0;  // initialize the rom address //this is the memory location for instructions

        foreach ($lines as $line) {
            if (preg_match('/^\((.+)\)$/', $line, $matches)) {
            // add the label to the symbol table with the current rom address
                $this->symbolTable[$matches[1]] = $romAddress;
                // we dont increment the rom address here because the label doesn't consume memory
            } else {
                 // if it's not a label, increment the rom address for the next instruction 
                $romAddress++;
            }
        }
    }

    private function generate(array $lines): string
    {
        //to convert the remaining code into binary //  A and C instructions
        $binary = [];

        foreach ($lines as $line) {
            if (preg_match('/^\(.+\)$/', $line)) continue; // we skip label lines

            if (str_starts_with($line, '@')) { // // if it's an A instruction 
                $symbol = substr($line, 1); //@R0 -> R0
                $address = $this->userSymbol($symbol);
                //we used decbin to convert the address to binary string , 
                // and we used str_pad to add zeros to the left 
                // becouse hack machine instructions must be 16 bits long
                $binary[] = str_pad(decbin($address), 16, '0', STR_PAD_LEFT);
            } else { //// it's a C instruction
                $binary[] = '111' . $this->translateCInstruction($line); //111 comp dest jump
            }
        }

        return implode("\n", $binary);
    }

    private function userSymbol(string $symbol): int
    {
        if (is_numeric($symbol)) return (int)$symbol;

        //if the symbol is not in the symbol table // assign it to a new address
        if (!isset($this->symbolTable[$symbol])) {
            $this->symbolTable[$symbol] = $this->nextAddress++;
        }

        return $this->symbolTable[$symbol];//return the address of the symbol //predefined or new one
    }

    private function translateCInstruction(string $line): string
    {
      //initialize variables
    $dest = '';
    $comp = '';
    $jump = '';

    // Both dest and jump
    if (str_contains($line, '=') && str_contains($line, ';')) {
        [$destPart, $rest] = explode('=', $line);
        [$compPart, $jumpPart] = explode(';', $rest);
        $dest = $destPart;
        $comp = $compPart;
        $jump = $jumpPart;
    // if only dest
    } elseif (str_contains($line, '=')) {
        [$dest, $comp] = explode('=', $line);
    // if only jump
    } elseif (str_contains($line, ';')) {
        [$comp, $jump] = explode(';', $line);
    // if only comp
    } else {
        $comp = $line;
    }

    // Check if the comp is 'A' and assign its binary correctly
    $compBinary = $this->comp[trim($comp)] ?? '0000000'; 
    $destBinary = $this->dest[trim($dest)] ?? '000'; 
    $jumpBinary = $this->jump[trim($jump)] ?? '000'; 

    return $compBinary . $destBinary . $jumpBinary;
    }
}
