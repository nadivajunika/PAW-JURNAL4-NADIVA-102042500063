<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'jawaban' => 'required|string|min:10|max:20000',
        ], [
            'jawaban.required' => 'Teks jawaban tugas wajib diisi.',
            'jawaban.min'      => 'Jawaban terlalu pendek. Tuliskan jawaban minimal 10 karakter.',
            'jawaban.max'      => 'Jawaban melebihi batas maksimal 20.000 karakter.',
        ]);

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id'       => $user->id,
            ],
            [
                'jawaban'      => $validated['jawaban'],
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas Anda berhasil disimpan ke sistem.');
    }

    public function grade(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'nilai'         => 'required|integer|min:0|max:100',
            'catatan_dosen' => 'nullable|string|max:1000',
        ]);

        $submission->update([
            'nilai'         => $validated['nilai'],
            'catatan_dosen' => $validated['catatan_dosen'] ?? null,
        ]);

        return back()->with('success', 'Nilai dan catatan dosen berhasil disimpan.');
    }

    public function exportCsv(Assignment $assignment)
    {
        $submissions = Submission::with('user')
            ->where('assignment_id', $assignment->id)
            ->get();

        $fileName = 'Rekap_Nilai_' . str_replace(' ', '_', $assignment->judul) . '.csv';

        return response()->streamDownload(function () use ($submissions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No',
                'Nama Mahasiswa',
                'NIM',
                'Email',
                'Waktu Pengumpulan',
                'Nilai',
                'Status Kelulusan',
                'Catatan Dosen',
            ]);

            foreach ($submissions as $index => $sub) {
                fputcsv($handle, [
                    $index + 1,
                    $sub->user->name ?? '-',
                    $sub->user->nomor_induk ?? '-',
                    $sub->user->email ?? '-',
                    $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i') . ' WIB' : '-',
                    $sub->nilai ?? 'Belum Dinilai',
                    $sub->status_kelulusan,
                    $sub->catatan_dosen ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}