import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../core/constants.dart';

class ProposalPage extends StatefulWidget {
  const ProposalPage({super.key});

  @override
  State<ProposalPage> createState() => _ProposalPageState();
}

class _ProposalPageState extends State<ProposalPage> {
  final _formKey = GlobalKey<FormState>();
  int _isOnline = 0;
  int? _selectedOrgId;
  bool _isLoading = false;
  bool _isFetchingOrgs = true;

  final _titleController = TextEditingController();
  final _dateController = TextEditingController();
  final _capacityController = TextEditingController();
  final _descController = TextEditingController();
  final _linkController = TextEditingController();
  final _locationNameController = TextEditingController();

  LatLng _selectedLocation = const LatLng(-6.97426, 107.6337);

  XFile? _posterFile;
  PlatformFile? _proposalFile;
  final ImagePicker _picker = ImagePicker();

  List<dynamic> _myOrganizations = [];

  @override
  void initState() {
    super.initState();
    _fetchOrganizations();
  }

  Future<void> _fetchOrganizations() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? token = prefs.getString('auth_token');

      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/student/organizations'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (!mounted) return;

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _myOrganizations = data['data'] ?? [];
          if (_myOrganizations.isNotEmpty) {
            _selectedOrgId = _myOrganizations[0]['id'];
          } else {
            final debugInfo = data['debug'] ?? 'Data Kosong';
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(debugInfo),
                duration: const Duration(seconds: 5),
                backgroundColor: Colors.red.shade800,
              ),
            );
          }
          _isFetchingOrgs = false;
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Sistem Server Menolak: ${response.statusCode}')),
        );
        setState(() => _isFetchingOrgs = false);
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Aplikasi gagal menyambung ke server utama')),
      );
      setState(() => _isFetchingOrgs = false);
    }
  }

  Future<void> _pickDateTime() async {
    DateTime? date = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime(2100),
    );
    if (date == null) return;

    if (!mounted) return;
    TimeOfDay? time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
    );
    if (time == null) return;

    setState(() {
      _dateController.text =
          "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')} ${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}:00";
    });
  }

  Future<void> _openMapDialog() async {
    LatLng tempLocation = _selectedLocation;
    final LatLng? picked = await showDialog<LatLng>(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return Dialog(
              insetPadding: const EdgeInsets.all(16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: SizedBox(
                  height: 500,
                  child: Stack(
                    children: [
                      FlutterMap(
                        options: MapOptions(
                          initialCenter: tempLocation,
                          initialZoom: 16.0,
                          onTap: (tapPosition, point) {
                            setDialogState(() {
                              tempLocation = point;
                            });
                          },
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
                                point: tempLocation,
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
                      Positioned(
                        bottom: 20,
                        left: 20,
                        right: 20,
                        child: ElevatedButton(
                          onPressed: () => Navigator.pop(context, tempLocation),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryColor,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: const Text(
                            'Konfirmasi Lokasi',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );

    if (picked != null) {
      setState(() {
        _selectedLocation = picked;
      });
    }
  }

  Future<void> _pickPoster() async {
    final XFile? image = await _picker.pickImage(source: ImageSource.gallery);
    if (image != null) {
      if (!mounted) return;
      setState(() => _posterFile = image);
    }
  }

  Future<void> _pickProposal() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf'],
      withData: true,
    );
    if (result != null) {
      if (!mounted) return;
      setState(() => _proposalFile = result.files.first);
    }
  }

  Future<void> _submitProposal() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedOrgId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih organisasi terlebih dahulu.')),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? token = prefs.getString('auth_token');

      var request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiConstants.baseUrl}/events'),
      );
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      request.fields['admin_id'] = _selectedOrgId.toString();
      request.fields['title'] = _titleController.text;
      request.fields['description'] = _descController.text;
      request.fields['event_date'] = _dateController.text;
      request.fields['capacity'] = _capacityController.text;
      request.fields['is_online'] = _isOnline.toString();

      if (_isOnline == 1) {
        request.fields['meeting_link'] = _linkController.text;
      } else {
        request.fields['latitude'] = _selectedLocation.latitude.toString();
        request.fields['longitude'] = _selectedLocation.longitude.toString();
        request.fields['location_name'] = _locationNameController.text;
      }

      if (_posterFile != null) {
        final bytes = await _posterFile!.readAsBytes();
        request.files.add(
          http.MultipartFile.fromBytes(
            'poster',
            bytes,
            filename: _posterFile!.name,
          ),
        );
      }

      if (_proposalFile != null && _proposalFile!.bytes != null) {
        request.files.add(
          http.MultipartFile.fromBytes(
            'proposal',
            _proposalFile!.bytes!,
            filename: _proposalFile!.name,
          ),
        );
      }

      var response = await request.send();
      var responseBody = await response.stream.bytesToString();
      var result = jsonDecode(responseBody);

      if (!mounted) return;

      if (response.statusCode == 200 || response.statusCode == 201) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Pengajuan berhasil.')));
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Gagal mengajukan acara.'),
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Koneksi terputus.')));
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Widget _buildTextField(
    String label,
    TextEditingController controller, {
    bool isNumber = false,
    int lines = 1,
    bool readOnly = false,
    VoidCallback? onTap,
    String? hint,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            color: AppColors.blackTextColor,
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          keyboardType: isNumber ? TextInputType.number : TextInputType.text,
          maxLines: lines,
          readOnly: readOnly,
          onTap: onTap,
          validator: (val) => val!.isEmpty ? 'Wajib diisi' : null,
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: AppColors.whiteColor,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppColors.borderLight),
            ),
          ),
        ),
        const SizedBox(height: 16),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Ajukan Acara',
          style: TextStyle(
            color: AppColors.blackTextColor,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: AppColors.whiteColor,
        iconTheme: const IconThemeData(color: AppColors.blackTextColor),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Organisasi',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: AppColors.blackTextColor,
                ),
              ),
              const SizedBox(height: 8),
              _isFetchingOrgs
                  ? const Center(child: CircularProgressIndicator())
                  : DropdownButtonFormField<int>(
                      initialValue: _selectedOrgId,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        filled: true,
                        fillColor: AppColors.whiteColor,
                      ),
                      items: _myOrganizations.isEmpty
                          ? [
                              const DropdownMenuItem<int>(
                                value: null,
                                child: Text('Tidak ada UKM terdaftar'),
                              ),
                            ]
                          : _myOrganizations
                                .map(
                                  (org) => DropdownMenuItem<int>(
                                    value: org['id'],
                                    child: Text(org['name']),
                                  ),
                                )
                                .toList(),
                      onChanged: (value) =>
                          setState(() => _selectedOrgId = value),
                    ),
              const SizedBox(height: 16),

              _buildTextField('Judul Acara', _titleController),
              _buildTextField(
                'Tanggal Acara',
                _dateController,
                readOnly: true,
                onTap: _pickDateTime,
                hint: 'Pilih Tanggal & Jam',
              ),
              _buildTextField('Kapasitas', _capacityController, isNumber: true),

              const Text(
                'Tipe Acara',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Row(
                children: [
                  Expanded(
                    child: RadioListTile<int>(
                      title: const Text('Offline'),
                      value: 0,
                      groupValue: _isOnline,
                      onChanged: (val) => setState(() => _isOnline = val!),
                    ),
                  ),
                  Expanded(
                    child: RadioListTile<int>(
                      title: const Text('Online'),
                      value: 1,
                      groupValue: _isOnline,
                      onChanged: (val) => setState(() => _isOnline = val!),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              if (_isOnline == 1) ...[
                _buildTextField('Link Lokasi / Meeting', _linkController),
              ] else ...[
                _buildTextField(
                  'Nama Gedung / Ruangan',
                  _locationNameController,
                  hint: 'Contoh: Gedung TULT Lantai 3',
                ),
                const Text(
                  'Titik Koordinat Peta',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                InkWell(
                  onTap: _openMapDialog,
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.whiteColor,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Buka Peta Interaktif',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: AppColors.primaryColor,
                          ),
                        ),
                        const Icon(Icons.map, color: AppColors.primaryColor),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],

              const Text(
                'Upload Dokumen',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _pickPoster,
                      icon: const Icon(Icons.image),
                      label: Text(
                        _posterFile != null ? 'Poster ✓' : 'Pilih Poster',
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _pickProposal,
                      icon: const Icon(Icons.picture_as_pdf),
                      label: Text(
                        _proposalFile != null ? 'Proposal ✓' : 'Pilih Proposal',
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              _buildTextField('Deskripsi Lengkap', _descController, lines: 4),

              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _submitProposal,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primaryColor,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: _isLoading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text(
                          'Ajukan Sekarang',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }
}
