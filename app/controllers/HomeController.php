<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Halaman Utama',
            'mainClass' => 'guest-content',
            'rooms' => (new Room())->all(),
        ]);
    }

    public function detail(): void
    {
        $room = (new Room())->find((int) ($_GET['id'] ?? 0));
        if (!$room) {
            http_response_code(404);
            echo '404 - Kamar tidak ditemukan';
            return;
        }

        $this->view('home/detail', [
            'title' => 'Detail Kamar ' . $room['room_number'],
            'mainClass' => 'guest-content',
            'room' => $room,
            'facilities' => $this->roomFacilities($room),
        ]);
    }

    private function roomFacilities(array $room): array
    {
        $facilities = [
            'Kasur dan bantal',
            'Lemari pakaian',
            'Meja belajar',
            'Kamar mandi dalam',
            'Akses WiFi kost',
        ];

        if ($room['type'] === 'AC') {
            array_unshift($facilities, 'AC pribadi');
        } else {
            array_unshift($facilities, 'Ventilasi udara dan kipas angin');
        }

        return $facilities;
    }
}
