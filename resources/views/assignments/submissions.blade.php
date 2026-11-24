@extends('layouts.app')

@section('title', 'Daftar Pengumpulan Tugas — ' . $assignment->judul)

@section('content')
@php
    $submissionsMap = $assignment->submissions->keyBy('user_id');

    if (isset($students)) {
        $daftarMahasiswa = $students;
    } elseif (isset($enrolledStudents)) {
        $daftarMahasiswa = $enrolledStudents;
    } elseif ($assignment->course && method_exists($assignment->course, 'students')) {
        $daftarMahasiswa = $assignment->course->students()->wherePivot('status', 'approved')->get();
    } else {
        $daftarMahasiswa = $assignment->submissions->map->user->filter();
    }

    if ($daftarMahasiswa->isEmpty() && $assignment->submissions->isNotEmpty()) {
        $daftarMahasiswa = $assignment->submissions->map->user->filter();
    }

    $totalMahasiswa = $daftarMahasiswa->count();
    $sudahMengumpulkan = $assignment->submissions->count();
    $rataRataNilai = $assignment->submissions->whereNotNull('nilai')->avg('nilai');
@endphp

<div style="margin-bottom: 1rem;">
    <a href="{{ route('courses.show', $assignment->course_id) }}" class="btn btn-secondary btn-sm">
        &larr; Kembali ke Mata Kuliah
    </a>
</div>

@if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.875rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem; font-weight: 500;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.875rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Kartu Informasi Tugas & Tombol Export CSV --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem;">
                <strong style="color: #0f172a;">{{ $assignment->course->kode_mk ?? '' }}</strong>
                &nbsp;{{ $assignment->course->nama_mk ?? '' }}
            </div>
            <h2 style="margin: 0 0 0.35rem 0; font-size: 1.35rem; font-weight: 700; color: #0f172a;">
                {{ $assignment->judul }}
            </h2>
            <p class="card-subtitle" style="margin: 0; font-size: 0.85rem; color: #64748b;">
                Batas Pengumpulan:
                <strong style="color: #334155;">{{ \Carbon\Carbon::parse($assignment->deadline)->format('l, d F Y — H:i') }} WIB</strong>
                @if(method_exists($assignment, 'isOverdue') && $assignment->isOverdue())
                    <span style="color: #dc2626; font-size: 0.8125rem; font-weight: 600; margin-left: 0.5rem;">Batas Waktu Berakhir</span>
                @else
                    <span style="color: #059669; font-size: 0.8125rem; font-weight: 600; margin-left: 0.5rem;">Sedang Berlangsung</span>
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('assignments.export-csv', $assignment) }}" class="btn btn-danger btn-sm">
                Export CSV
            </a>
            <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-secondary btn-sm">
                Edit Tugas
            </a>
        </div>
    </div>

    <div style="padding-top: 1rem; border-top: 1px solid #e2e8f0; margin-top: 1rem;">
        <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; color: #64748b; text-transform: uppercase; margin-bottom: 0.4rem;">
            PETUNJUK PENUGASAN:
        </div>
        <p style="margin: 0; color: #334155; font-size: 0.925rem; line-height: 1.6;">
            {{ $assignment->deskripsi }}
        </p>
    </div>
</div>

{{-- Kartu Ringkasan & Rata-Rata Nilai Otomatis --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem;">
        <div style="font-size: 0.8125rem; color: #64748b; font-weight: 500; margin-bottom: 0.35rem;">Total Mahasiswa</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $totalMahasiswa }}</div>
    </div>
    <div class="card" style="padding: 1.25rem;">
        <div style="font-size: 0.8125rem; color: #64748b; font-weight: 500; margin-bottom: 0.35rem;">Sudah Mengumpulkan</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $sudahMengumpulkan }}</div>
    </div>
    <div class="card" style="padding: 1.25rem;">
        <div style="font-size: 0.8125rem; color: #64748b; font-weight: 500; margin-bottom: 0.35rem;">Rata-rata Nilai</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #2563eb;">
            {{ $rataRataNilai !== null ? round($rataRataNilai, 2) : 0 }}
        </div>
    </div>
</div>

{{-- Tabel Rekapitulasi Pengumpulan & Penilaian Mahasiswa --}}
<div class="card">
    <div style="margin-bottom: 1.25rem;">
        <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; font-weight: 700; color: #0f172a;">
            Rekapitulasi Pengumpulan &amp; Penilaian Mahasiswa
        </h3>
        <p style="margin: 0; font-size: 0.85rem; color: #64748b;">
            Semua mahasiswa yang terdaftar resmi di kelas mata kuliah ini
        </p>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">
                    <th style="padding: 0.75rem 0.5rem;">NO</th>
                    <th style="padding: 0.75rem 0.5rem;">MAHASISWA</th>
                    <th style="padding: 0.75rem 0.5rem;">NIM</th>
                    <th style="padding: 0.75rem 0.5rem;">STATUS &amp; WAKTU</th>
                    <th style="padding: 0.75rem 0.5rem;">JAWABAN TEKS</th>
                    <th style="padding: 0.75rem 0.5rem;">PENILAIAN DOSEN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarMahasiswa as $index => $mhs)
                    @php
                        $sub = $submissionsMap->get($mhs->id);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; vertical-align: middle;">
                        <td style="padding: 1rem 0.5rem; color: #334155;">{{ $index + 1 }}</td>
                        <td style="padding: 1rem 0.5rem;">
                            <div style="font-weight: 600; color: #0f172a;">{{ $mhs->name }}</div>
                            <div style="font-size: 0.8rem; color: #64748b;">{{ $mhs->email }}</div>
                        </td>
                        <td style="padding: 1rem 0.5rem; font-weight: 600; color: #0f172a;">
                            {{ $mhs->nomor_induk ?? '-' }}
                        </td>
                        <td style="padding: 1rem 0.5rem;">
                            @if($sub)
                                <div style="color: #059669; font-weight: 600; font-size: 0.85rem;">Sudah Mengumpulkan</div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 0.35rem;">
                                    {{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i') . ' WIB' : '-' }}
                                </div>
                                @if(!is_null($sub->nilai))
                                    <span style="display: inline-block; padding: 0.2rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: {{ $sub->nilai >= 75 ? '#dcfce7' : '#fee2e2' }}; color: {{ $sub->nilai >= 75 ? '#166534' : '#991b1b' }};">
                                        {{ $sub->nilai }}/100 ({{ $sub->status_kelulusan }})
                                    </span>
                                @else
                                    <span style="font-size: 0.78rem; color: #94a3b8;">Belum Dinilai</span>
                                @endif
                            @else
                                <span style="color: #dc2626; font-weight: 600; font-size: 0.85rem;">Belum Mengumpulkan</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 0.5rem; max-width: 240px;">
                            @if($sub)
                                <details>
                                    <summary style="color: #dc2626; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                                        Lihat Jawaban ({{ strlen($sub->jawaban) }} Karakter)
                                    </summary>
                                    <div style="margin-top: 0.5rem; padding: 0.65rem; background: #f8fafc; border-radius: 6px; font-size: 0.825rem; color: #334155; line-height: 1.5;">
                                        {{ $sub->jawaban }}
                                    </div>
                                </details>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 0.5rem;">
                            @if($sub)
                                <form action="{{ route('submissions.grade', $sub->id) }}" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="nilai" min="0" max="100"
                                           value="{{ $sub->nilai }}" placeholder="0-100" required
                                           style="width: 72px; padding: 0.45rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">
                                    <input type="text" name="catatan_dosen"
                                           value="{{ $sub->catatan_dosen }}" placeholder="Catatan dosen..."
                                           style="width: 150px; padding: 0.45rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">
                                    <button type="submit" class="btn btn-danger btn-sm" style="white-space: nowrap;">
                                        Simpan Nilai
                                    </button>
                                </form>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem;">Menunggu pengumpulan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 1.5rem; text-align: center; color: #64748b;">
                            Belum ada mahasiswa yang terdaftar di kelas ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection