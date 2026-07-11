import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';

class EventDetailPage extends StatefulWidget {
  final Map<String, dynamic> event;

  const EventDetailPage({super.key, required this.event});

  @override
  State<EventDetailPage> createState() => _EventDetailPageState();
}

class _EventDetailPageState extends State<EventDetailPage> {
  bool _isRegistering = false;

  Future<void> _registerEvent() async {
    if (widget.event['id'] == null) {
      _showSnackbar('Gagal ID acara tidak ditemukan.');
      return;
    }
    setState(() => _isRegistering = true);

    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? token = prefs.getString('auth_token');

      final response = await http.post(
        Uri.parse(
          '${ApiConstants.baseUrl}/student/events/${widget.event['id']}/register',
        ),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      final result = jsonDecode(response.body);

      if (response.statusCode == 200 || response.statusCode == 201) {
        _showSnackbar('Berhasil mendaftar! Cek menu Tiketku.');
      } else {
        _showSnackbar(result['message'] ?? 'Gagal mendaftar acara.');
      }
    } catch (e) {
      _showSnackbar('Koneksi ke server terputus.');
    } finally {
      setState(() => _isRegistering = false);
    }
  }

  void _showSnackbar(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  String _getOrganizationName() {
    if (widget.event['panitia'] != null) {
      return widget.event['panitia']['organization'] ??
          widget.event['panitia']['name'] ??
          'Penyelenggara';
    }
    return widget.event['organization_name'] ?? 'Penyelenggara';
  }

  @override
  Widget build(BuildContext context) {
    final isOnline =
        widget.event['is_online'] == 1 || widget.event['is_online'] == true;
    final lat =
        double.tryParse(widget.event['latitude']?.toString() ?? '0') ??
        -6.97426;
    final lng =
        double.tryParse(widget.event['longitude']?.toString() ?? '0') ??
        107.6337;
    String orgName = _getOrganizationName();

    int capacity = int.tryParse(widget.event['capacity'].toString()) ?? 0;
    int registered =
        int.tryParse(widget.event['registrations_count'].toString()) ?? 0;
    int sisaKuota = capacity - registered;
    if (sisaKuota < 0) sisaKuota = 0;

    return Scaffold(
      backgroundColor: AppColors.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Detail Acara',
          style: TextStyle(color: AppColors.blackTextColor, fontSize: 18),
        ),
        backgroundColor: AppColors.whiteColor,
        iconTheme: const IconThemeData(color: AppColors.blackTextColor),
        elevation: 1,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.event['title'] ?? 'Judul Acara',
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppColors.blackTextColor,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.business, size: 16, color: Colors.grey),
                const SizedBox(width: 8),
                Text(
                  orgName,
                  style: const TextStyle(
                    color: Colors.grey,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            const Divider(height: 32, thickness: 1),

            Row(
              children: [
                const Icon(Icons.calendar_today, color: AppColors.primaryColor),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Waktu Pelaksanaan',
                        style: TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                      Text(
                        widget.event['event_date'] ?? '-',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                const Icon(Icons.people, color: AppColors.primaryColor),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Kapasitas Peserta',
                        style: TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                      Text(
                        '$capacity Orang (Sisa Kuota: $sisaKuota)',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 32, thickness: 1),

            const Text(
              'Deskripsi Acara',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              widget.event['description'] ??
                  'Tidak ada deskripsi yang dilampirkan.',
              style: const TextStyle(height: 1.5, color: Colors.black87),
            ),
            const SizedBox(height: 24),

            const Text(
              'Lokasi',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            if (isOnline)
              Container(
                padding: const EdgeInsets.all(16),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Acara Online Virtual',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: AppColors.primaryColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      widget.event['meeting_link'] ?? 'Tautan belum tersedia',
                    ),
                  ],
                ),
              )
            else
              SizedBox(
                height: 200,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: FlutterMap(
                    options: MapOptions(
                      initialCenter: LatLng(lat, lng),
                      initialZoom: 16.0,
                      interactionOptions: const InteractionOptions(
                        flags: InteractiveFlag.none,
                      ),
                    ),
                    children: [
                      TileLayer(
                        urlTemplate:
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.nexevent.app',
                      ),
                      MarkerLayer(
                        markers: [
                          Marker(
                            point: LatLng(lat, lng),
                            width: 40,
                            height: 40,
                            child: const Icon(
                              Icons.location_pin,
                              color: Colors.red,
                              size: 40,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 100),
          ],
        ),
      ),

      bottomSheet: Container(
        padding: const EdgeInsets.all(20),
        decoration: const BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              blurRadius: 10,
              offset: Offset(0, -2),
            ),
          ],
        ),
        child: SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton(
            onPressed: _isRegistering ? null : _registerEvent,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryColor,
            ),
            child: _isRegistering
                ? const CircularProgressIndicator(color: Colors.white)
                : const Text(
                    'Daftar Sekarang',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
          ),
        ),
      ),
    );
  }
}
