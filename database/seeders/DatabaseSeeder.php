<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Direktorat Kemahasiswaan',
            'email' => 'dirmawa@telkomuniversity.ac.id',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'status' => 'active'
        ]);
        
        $organisasiTelU = [
            ['name' => 'Admin BEM KEMA Tel-U', 'email' => 'bemkema@student.telkomuniversity.ac.id', 'org' => 'BEM KEMA'],
            ['name' => 'Humas SEARCH', 'email' => 'search@student.telkomuniversity.ac.id', 'org' => 'SEARCH Tel-U'],
            ['name' => 'Panitia LDK Al Fath', 'email' => 'alfath@student.telkomuniversity.ac.id', 'org' => 'LDK Al Fath'],
            ['name' => 'Pengurus Tel-U Choir', 'email' => 'choir@student.telkomuniversity.ac.id', 'org' => 'UKM Tel-U Choir'],
            ['name' => 'KMPA Tel-U', 'email' => 'kmpa@student.telkomuniversity.ac.id', 'org' => 'KMPA (Pecinta Alam)'],
        ];

        $panitiaList = collect();
        foreach ($organisasiTelU as $org) {
            $panitiaList->push(User::create([
                'name' => $org['name'],
                'email' => $org['email'],
                'password' => bcrypt('password'),
                'organization' => $org['org'],
                'role' => 'admin',
                'status' => 'active'
            ]));
        }

        $mahasiswas = collect();
        for ($i = 1; $i <= 200; $i++) {
            $mahasiswas->push(User::create([
                'name' => 'Mahasiswa Tel-U ' . $i,
                'email' => 'mahasiswa' . $i . '@student.telkomuniversity.ac.id',
                'password' => bcrypt('password'),
                'role' => 'user',
                'status' => 'active'
            ]));
        }

        $eventData = [
            [
                'title' => 'Seminar Artificial Intelligence: Masa Depan Teknologi',
                'user_id' => $panitiaList[1]->id,
                'capacity' => 150,
                'is_online' => false,
                'status' => 'approved',
                'pendaftar' => 152 
            ],
            [
                'title' => 'Workshop Jurnalistik & Kepenulisan Digital',
                'user_id' => $panitiaList[0]->id,
                'capacity' => 40,
                'is_online' => false,
                'status' => 'pending',
                'pendaftar' => 0 
            ],
            [
                'title' => 'Tabligh Akbar Menyambut Ramadhan',
                'user_id' => $panitiaList[2]->id,
                'capacity' => 100,
                'is_online' => true,
                'status' => 'approved',
                'pendaftar' => 100 
            ],
            [
                'title' => 'Ekspedisi Pendakian Gunung Gede',
                'user_id' => $panitiaList[4]->id, 
                'capacity' => 20,
                'is_online' => false,
                'status' => 'rejected',
                'reject_reason' => 'Proposal ditolak karena tidak melampirkan surat izin medis peserta dan SOP evakuasi.',
                'pendaftar' => 0 
            ],
            [
                'title' => 'Konser Virtual Tel-U Choir 2026',
                'user_id' => $panitiaList[3]->id, 
                'capacity' => 50,
                'is_online' => true,
                'status' => 'approved',
                'pendaftar' => 65 
            ]
        ];

        foreach ($eventData as $data) {
            $event = Event::create([
                'event_code' => 'EVT-' . strtoupper(Str::random(5)),
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'description' => 'Ini adalah deskripsi lengkap untuk acara ' . $data['title'] . ' yang diselenggarakan di Telkom University.',
                'event_date' => Carbon::now()->addDays(rand(10, 30))->format('Y-m-d H:i:00'),
                'capacity' => $data['capacity'],
                'is_online' => $data['is_online'],
                'status' => $data['status'],
                'reject_reason' => $data['reject_reason'] ?? null,
            ]);

            $jumlahPendaftar = $data['pendaftar'];
            if ($jumlahPendaftar > 0) {
                $pesertaAcak = $mahasiswas->random($jumlahPendaftar);
                
                $urutan = 1;
                foreach ($pesertaAcak as $mhs) {
                    $statusAntrean = ($urutan <= $event->capacity) ? 'utama' : 'waitlist';

                    Registration::create([
                        'reg_code' => 'REG-' . date('Ym') . '-' . str_pad($event->id, 2, '0', STR_PAD_LEFT) . str_pad($urutan, 3, '0', STR_PAD_LEFT),
                        'event_id' => $event->id,
                        'user_id' => $mhs->id,
                        'status' => $statusAntrean
                    ]);
                    $urutan++;
                }
            }
        }
    }
}