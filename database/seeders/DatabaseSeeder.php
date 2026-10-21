<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Pengguna ----
        $admin = User::factory()->admin()->create([
            'name' => 'Admin EventKu',
            'email' => 'admin@eventku.test',
            'password' => 'password',
        ]);

        $demo = User::factory()->create([
            'name' => 'Natasya Putri',
            'email' => 'user@eventku.test',
            'password' => 'password',
        ]);

        $users = collect([$demo])->merge(User::factory(24)->create());

        // ---- Kategori ----
        $categories = collect([
            ['Musik', 'music', 'Konser, festival, dan pertunjukan live.'],
            ['Teknologi', 'cpu', 'Konferensi, workshop, dan hackathon.'],
            ['Seni & Budaya', 'palette', 'Pameran, teater, dan kegiatan budaya.'],
            ['Olahraga', 'run', 'Lari, gowes, dan turnamen komunitas.'],
            ['Pendidikan', 'book', 'Seminar, kelas, dan pelatihan.'],
            ['Kuliner', 'cup', 'Festival makanan dan kelas memasak.'],
        ])->mapWithKeys(fn ($c) => [
            $c[1] => Category::create(['name' => $c[0], 'slug' => Str::slug($c[0]), 'icon' => $c[1], 'description' => $c[2]]),
        ]);

        $lorem = fn (string $intro) => $intro."\n\nAcara ini dirancang agar nyaman diikuti oleh peserta dari berbagai latar belakang. Panitia menyiapkan rangkaian kegiatan yang padat namun santai, lengkap dengan sesi interaksi, dokumentasi, dan kesempatan membangun jejaring.\n\nPeserta disarankan hadir 30 menit sebelum acara dimulai untuk registrasi ulang dengan menunjukkan kode registrasi dari EventKu.";

        // judul, kategori, poster, kota, lokasi, penyelenggara, offset hari mulai, durasi jam, kuota, harga, status, unggulan
        $rows = [
            ['Senja di Kota Fest 2026', 'music', 'poster-music', 'Jakarta', 'Lapangan Banteng', 'Senja Collective', 12, 8, 500, 150000, 'published', true, 'Festival musik akhir pekan dengan deretan penampil indie terbaik tanah air.'],
            ['Laravel Jakarta Summit', 'cpu', 'poster-tech', 'Jakarta', 'Balai Sidang Senayan', 'Komunitas Laravel ID', 20, 7, 300, 250000, 'published', true, 'Satu hari penuh membahas arsitektur, performa, dan praktik terbaik Laravel.'],
            ['Pameran Batik Nusantara', 'palette', 'poster-art', 'Yogyakarta', 'Taman Budaya Yogyakarta', 'Rumah Batik Kita', 8, 6, 200, 0, 'published', true, 'Menjelajahi motif batik dari berbagai daerah bersama para pengrajin.'],
            ['Sunrise Fun Run 5K', 'run', 'poster-sport', 'Bandung', 'Lapangan Gasibu', 'Bandung Runners', 5, 3, 400, 50000, 'published', true, 'Lari santai 5K saat matahari terbit, cocok untuk pemula dan keluarga.'],
            ['Workshop UI/UX untuk Pemula', 'cpu', 'poster-tech', 'Bandung', 'Co-working Dago', 'Desain Bareng', 15, 4, 40, 100000, 'published', false, 'Belajar dasar riset pengguna dan prototyping langsung bersama praktisi.'],
            ['Konser Akustik Malam Minggu', 'music', 'poster-music', 'Surabaya', 'Balai Pemuda', 'Akustik Sby', 3, 3, 120, 75000, 'published', false, 'Malam santai dengan lagu-lagu akustik dan kopi hangat.'],
            ['Bazar Kuliner Nusantara', 'cup', 'poster-art', 'Malang', 'Alun-Alun Tugu', 'Malang Foodies', 25, 9, 600, 0, 'published', false, 'Ratusan tenant makanan khas dari seluruh Indonesia dalam satu tempat.'],
            ['Seminar Karier Fresh Graduate', 'book', 'poster-tech', 'Jakarta', 'Kampus Binus Kemanggisan', 'Career Hub ID', 18, 4, 150, 0, 'published', false, 'Tips CV, wawancara, dan membangun personal branding bersama HR profesional.'],
            ['Yoga & Mindfulness Pagi', 'run', 'poster-sport', 'Yogyakarta', 'Candi Prambanan Garden', 'Yoga Jogja', 7, 2, 30, 60000, 'published', false, 'Sesi yoga pagi terbuka dengan pemandangan candi.'],
            ['Workshop Memasak Masakan Padang', 'cup', 'poster-art', 'Bandung', 'Dapur Komunitas Cihampelas', 'Dapur Bersama', 10, 3, 20, 120000, 'published', false, 'Praktik langsung membuat rendang dan gulai bersama chef lokal.'],
            ['Hackathon Kota Cerdas', 'cpu', 'poster-tech', 'Surabaya', 'ITS Convention Hall', 'Surabaya Digital', 30, 24, 80, 0, 'published', false, 'Hackathon 24 jam untuk solusi transportasi dan lingkungan kota.'],
            ['Malam Puisi & Musikalisasi', 'palette', 'poster-art', 'Yogyakarta', 'Bentara Budaya', 'Sanggar Aksara', 14, 3, 100, 25000, 'closed', false, 'Pendaftaran sudah ditutup — sampai jumpa di acara berikutnya.'],
            ['Intimate Jazz Night', 'music', 'poster-music', 'Jakarta', 'Rooftop Kemang', 'Jazz Kemang', 6, 3, 25, 200000, 'published', false, 'Konser jazz intim dengan kapasitas sangat terbatas.'],
            ['Pekan Bahasa Inggris Kampus', 'book', 'poster-tech', 'Malang', 'Universitas Brawijaya', 'English Club UB', -20, 5, 200, 0, 'published', false, 'Rangkaian lomba debat dan storytelling (sudah selesai).'],
            ['Festival Layang-Layang Pantai', 'run', 'poster-sport', 'Surabaya', 'Pantai Ria Kenjeran', 'Komunitas Layangan', -45, 6, 300, 0, 'published', false, 'Festival layang-layang tahunan di tepi pantai (sudah selesai).'],
            ['Konferensi Startup Muda', 'cpu', 'poster-tech', 'Jakarta', 'Jakarta Convention Center', 'Startup Muda ID', 45, 8, 1000, 350000, 'draft', false, 'Konferensi startup (masih draft, belum dipublikasikan).'],
        ];

        foreach ($rows as $r) {
            $start = now()->addDays($r[6])->setTime(rand(8, 17), 0);
            Event::create([
                'category_id' => $categories[$r[1]]->id,
                'created_by' => $admin->id,
                'title' => $r[0],
                'slug' => Str::slug($r[0]),
                'description' => $lorem($r[12]),
                'poster' => 'images/'.$r[2].'.jpg',
                'organizer' => $r[5],
                'location' => $r[4],
                'city' => $r[3],
                'starts_at' => $start,
                'ends_at' => (clone $start)->addHours($r[7]),
                'quota' => $r[8],
                'price' => $r[9],
                'status' => $r[10],
                'is_featured' => $r[11],
            ]);
        }

        // ---- Pendaftaran realistis (tidak melebihi kuota; satu event dibuat penuh) ----
        $events = Event::visible()->get();
        foreach ($events as $event) {
            $take = $event->title === 'Intimate Jazz Night'
                ? 25
                : min($event->quota - 1, rand(3, max(3, min(20, $users->count()))));

            foreach ($users->shuffle()->take(min($take, $users->count())) as $user) {
                Registration::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'code' => Registration::generateCode(),
                    'status' => $event->ends_at->isPast() ? Registration::STATUS_ATTENDED : Registration::STATUS_CONFIRMED,
                    'registered_at' => now()->subDays(rand(0, 14))->subMinutes(rand(0, 600)),
                ]);
            }
        }
    }
}
