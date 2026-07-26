<?php

namespace App\Helpers;

use App\Models\Administration\System\DocumentNumbering;
use Illuminate\Support\Facades\DB;

class DocNumberHelper
{
    public static function generate($module, $departmentId = null)
    {
        return DB::transaction(function () use ($module, $departmentId) {

            $numbering = DocumentNumbering::where('module', $module)
                ->where('department', $departmentId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$numbering) {
                throw new \Exception("Document Numbering untuk module '{$module}' belum dikonfigurasi.");
            }

            $today = now();

            $currentPeriod = match ($numbering->reset_type) {

                'DAILY' => $today->format('Ymd'),

                'MONTHLY' => $today->format('Ym'),

                'YEARLY' => $today->format('Y'),

                default => null,
            };

            if (
                $numbering->reset_type != 'NEVER' &&
                $numbering->last_reset_period != $currentPeriod
            ) {

                $numbering->current_sequence = 0;
                $numbering->last_reset_period = $currentPeriod;
            }

            $numbering->current_sequence++;

            $numbering->save();

            $seq = str_pad(
                $numbering->current_sequence,
                $numbering->digit_length,
                '0',
                STR_PAD_LEFT
            );

            $doc = $numbering->format;

            $doc = str_replace('{PREFIX}', $numbering->prefix, $doc);
            $doc = str_replace('{YYYY}', $today->format('Y'), $doc);
            $doc = str_replace('{YY}', $today->format('y'), $doc);
            $doc = str_replace('{MM}', $today->format('m'), $doc);
            $doc = str_replace('{DD}', $today->format('d'), $doc);
            $doc = str_replace('{SEQ}', $seq, $doc);

            return $doc;
        });
    }
}
