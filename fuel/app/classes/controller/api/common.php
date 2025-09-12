<?php
// APPPATH/classes/controller/api/common.php
class Controller_Api_Common
{
    public static function successResponse($data = [], $message = 'OK')
    {
        return [
            'ok' => true,
            'message' => $message,
            'items' => $data,
        ];
    }

    public static function errorResponse($message = 'Error', $status = 500)
    {
        return [
            'ok' => false,
            'message' => $message,
            'status' => $status,
        ];
    }

    // 必要最低限：シフト1件をAPI用フォーマットに
    public static function formatShiftData(array $s)
    {
        return [
            'id'           => (int)($s['id'] ?? $s['id'] ?? 0),
            'created_by'   => isset($s['created_by']) ? (int)$s['created_by'] : null,
            'shift_date'   => $s['shift_date'] ?? null,
            'start_time'   => $s['start_time'] ?? null,
            'end_time'     => $s['end_time'] ?? null,
            'recruit_count'=> isset($s['recruit_count']) ? (int)$s['recruit_count'] : (isset($s['slot_count']) ? (int)$s['slot_count'] : 1),
            'free_text'    => $s['free_text'] ?? ($s['note'] ?? null),
        ];
    }
}