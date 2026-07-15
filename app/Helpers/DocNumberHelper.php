<?php

namespace App\Helpers;

use App\Models\Administration\System\DocumentNumbering as SystemDocumentNumbering;

class DocNumberHelper
{
    public static function generate($moduleCode, $departmentCode)
    {
        $numbering = SystemDocumentNumbering::where('module', $moduleCode)->where('department', $departmentCode)->where('is_active', true)->first();

        if (!$numbering) {
            return $moduleCode . '-' . $departmentCode . '-' . time(); // Fallback jika format belum diatur admin
        }

        // Naikkan nomor urutan secara berkala
        $numbering->increment('current_sequence');
        $seq = str_pad($numbering->current_sequence, $numbering->digit_length, '0', STR_PAD_LEFT);

        $result = $numbering->format;
        $result = str_replace('{PREFIX}', $numbering->prefix, $result);
        $result = str_replace('{YYYY}', date('Y'), $result);
        $result = str_replace('{YY}', date('y'), $result);
        $result = str_replace('{MM}', date('m'), $result);
        $result = str_replace('{DD}', date('d'), $result);
        $result = str_replace('{SEQ}', $seq, $result);

        return $result;
    }
}
