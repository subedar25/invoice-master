<?php

namespace App\Core\TimeOff\Services;

use App\Core\TimeOff\Contracts\TimeOffRequestRepository;
use Illuminate\Support\Carbon;

class TimeOffRequestService
{
    private $requestRepository;

    public function __construct(TimeOffRequestRepository $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    public function getAllRequests(array $filters = [], int $perPage = 10)
    {
        return $this->requestRepository->getRequests($filters, $perPage);
    }

    public function getDataTableData(array $filters, ?string $search, int $start, int $length, array $order)
    {
        $sortColumn = $order['column'] ?? 'added_timestamp';
        $sortDir = $order['dir'] ?? 'desc';

        $data = $this->requestRepository->getForDataTable($filters, $search, $start, $length, $sortColumn, $sortDir);
        $totalDisplay = $this->requestRepository->countRequests($filters, $search);
        $totalAll = $this->requestRepository->countRequests([], null);

        return [
            'data' => $data,
            'recordsFiltered' => $totalDisplay, // DataTables expects this for pagination
            'recordsTotal' => $totalAll,
        ];
    }

    public function bulkUpdateStatusByFilter(array $filters, string $status)
    {
        return $this->requestRepository->updateByFilter($filters, ['status' => $status]);
    }

    public function createRequest(array $data)
    {
        $data['paid'] = isset($data['paid']) ? (bool) $data['paid'] : false;
        
        // Default submitted to 1 if not present, though migration defaults it too
        if (!isset($data['submitted'])) {
            $data['submitted'] = true;
        }

        return $this->requestRepository->create($data);
    }

    public function updateRequest(int $id, array $data)
    {
        if (isset($data['paid'])) {
            $data['paid'] = (bool) $data['paid'];
        }
        return $this->requestRepository->update($id, $data);
    }

    public function bulkUpdateStatus(array $ids, string $status)
    {
        return $this->requestRepository->updateMultiple($ids, ['status' => $status]);
    }

    public function deleteRequest(int $id)
    {
        return $this->requestRepository->delete($id);
    }

    public function getRequest(int $id)
    {
        return $this->requestRepository->find($id);
    }

    public function exportIds(string $type, array $filters = [])
    {
        $filename = "time-off-requests-" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'ID', 
                'User Name', 
                'Start Time', 
                'End Time', 
                'Paid', 
                'Status', 
                'Notes',
                'Added Timestamp',
                'Submitted', // Backend only, but including in export maybe useful?
                'Timesheet ID'
            ]);

            // Get all requests matching filters (perPage = 0)
            $requests = $this->requestRepository->getRequests($filters, 0);

            foreach ($requests as $request) {
                fputcsv($handle, [
                    $request->id,
                    $request->user ? $request->user->first_name . ' ' . $request->user->last_name : 'N/A',
                    $request->start_time,
                    $request->end_time,
                    $request->paid ? 'Yes' : 'No',
                    ucfirst(str_replace('_', ' ', $request->status)),
                    $request->notes,
                    $request->added_timestamp,
                    $request->submitted ? 'Yes' : 'No',
                    $request->timesheet_id
                ]);
            }

            fclose($handle);
        }, $filename);
    }
}
