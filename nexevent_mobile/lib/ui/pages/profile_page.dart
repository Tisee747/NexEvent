import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';
import 'login_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  bool _isLoading = true;
  bool _isSaving = false;
  bool _isEditing = false;

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _nimController = TextEditingController();
  final _fakultasController = TextEditingController();
  final _prodiController = TextEditingController();
  final _angkatanController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  Future<void> _loadProfileData() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    try {
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (!mounted) return;

      if (response.statusCode == 200) {
        final userData = jsonDecode(response.body);
        setState(() {
          _nameController.text = userData['name'] ?? '';
          _emailController.text = userData['email'] ?? '';
          _nimController.text = userData['nim'] ?? '';
          _fakultasController.text = userData['fakultas'] ?? '';
          _prodiController.text = userData['program_studi'] ?? '';
          _angkatanController.text = userData['angkatan']?.toString() ?? '';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  Future<void> _updateProfile() async {
    setState(() => _isSaving = true);
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    try {
      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/user/update'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          '_method': 'PUT',
          'name': _nameController.text,
          'fakultas': _fakultasController.text,
          'program_studi': _prodiController.text,
          'angkatan': _angkatanController.text,
        }),
      );

      if (!mounted) return;

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profil berhasil diperbarui!')),
        );
        setState(() => _isEditing = false);
        _loadProfileData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal memperbarui profil.')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Koneksi terputus.')));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _logout() async {
    bool confirm =
        await showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text(
              'Logout',
              style: TextStyle(
                color: AppColors.blackTextColor,
                fontWeight: FontWeight.bold,
              ),
            ),
            content: const Text('Yakin ingin keluar dari aplikasi?'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Batal'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                child: const Text(
                  'Keluar',
                  style: TextStyle(color: Colors.white),
                ),
              ),
            ],
          ),
        ) ??
        false;

    if (confirm) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (context) => const LoginPage()),
          (route) => false,
        );
      }
    }
  }

  Widget _buildTextField(
    String label,
    TextEditingController controller,
    bool isLocked,
  ) {
    bool isReadOnly = !_isEditing || isLocked;
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: AppColors.greyTextColor,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: controller,
            readOnly: isReadOnly,
            style: TextStyle(
              color: isReadOnly
                  ? AppColors.greyTextColor
                  : AppColors.blackTextColor,
            ),
            decoration: InputDecoration(
              filled: true,
              fillColor: isReadOnly
                  ? AppColors.borderLight.withOpacity(0.3)
                  : AppColors.cardColor,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: isReadOnly
                      ? Colors.transparent
                      : AppColors.borderLight,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundColor,
      body: SafeArea(
        child: _isLoading
            ? const Center(
                child: CircularProgressIndicator(color: AppColors.primaryColor),
              )
            : SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: const BoxDecoration(
                        border: Border(
                          bottom: BorderSide(color: AppColors.borderLight),
                        ),
                      ),
                      child: const Text(
                        'Profil Saya',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: AppColors.blackTextColor,
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),

                    CircleAvatar(
                      radius: 50,
                      backgroundColor: AppColors.lightBlue.withOpacity(0.4),
                      child: const Icon(
                        Icons.person,
                        size: 50,
                        color: AppColors.primaryColor,
                      ),
                    ),
                    const SizedBox(height: 32),

                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildTextField(
                            'Nama Lengkap',
                            _nameController,
                            false,
                          ),
                          _buildTextField(
                            'Email Kampus',
                            _emailController,
                            true,
                          ),
                          _buildTextField('NIM', _nimController, true),
                          _buildTextField(
                            'Fakultas',
                            _fakultasController,
                            false,
                          ),
                          _buildTextField(
                            'Program Studi',
                            _prodiController,
                            false,
                          ),
                          _buildTextField(
                            'Angkatan',
                            _angkatanController,
                            false,
                          ),
                          const SizedBox(height: 16),

                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton.icon(
                              onPressed: _isEditing
                                  ? _updateProfile
                                  : () => setState(() => _isEditing = true),
                              icon: Icon(
                                _isEditing ? Icons.save : Icons.edit,
                                color: Colors.white,
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primaryColor,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              label: _isSaving
                                  ? const CircularProgressIndicator(
                                      color: Colors.white,
                                    )
                                  : Text(
                                      _isEditing
                                          ? 'Simpan Perubahan'
                                          : 'Edit Profil',
                                      style: const TextStyle(
                                        color: AppColors.whiteColor,
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                            ),
                          ),
                          const SizedBox(height: 24),
                          const Divider(
                            color: AppColors.borderLight,
                            thickness: 1.5,
                          ),
                          const SizedBox(height: 8),

                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: OutlinedButton.icon(
                              onPressed: _logout,
                              icon: const Icon(Icons.logout, color: Colors.red),
                              label: const Text(
                                'Keluar dari Akun',
                                style: TextStyle(
                                  color: Colors.red,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(
                                  color: Colors.red,
                                  width: 1.5,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 40),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
