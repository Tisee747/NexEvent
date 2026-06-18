import 'package:flutter/material.dart';
import '../../core/constants.dart';
import '../../services/auth_service.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({Key? key}) : super(key: key);

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _otpController = TextEditingController();
  final _nimController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  bool _isSendingOtp = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  String? _selectedFakultas;
  String? _selectedProdi;

  final List<String> _fakultasOptions = [
    'Fakultas Informatika',
    'Fakultas Teknik Elektro',
    'Fakultas Rekayasa Industri',
    'Fakultas Ekonomi dan Bisnis',
    'Fakultas Komunikasi dan Bisnis',
    'Fakultas Industri Kreatif',
    'Fakultas Ilmu Terapan',
  ];
  final Map<String, List<String>> _prodiOptions = {
    'Fakultas Informatika': [
      'S1 Informatika',
      'S1 Teknologi Informasi',
      'S1 Rekayasa Perangkat Lunak',
      'S1 Sains Data',
    ],
    'Fakultas Teknik Elektro': [
      'S1 Teknik Telekomunikasi',
      'S1 Teknik Elektro',
      'S1 Teknik Komputer',
      'S1 Teknik Biomedis',
    ],
    'Fakultas Rekayasa Industri': [
      'S1 Teknik Industri',
      'S1 Sistem Informasi',
      'S1 Teknik Logistik',
    ],
    'Fakultas Ekonomi dan Bisnis': [
      'S1 Manajemen Bisnis Telekomunikasi & Informatika',
      'S1 Akuntansi',
    ],
    'Fakultas Komunikasi dan Bisnis': [
      'S1 Ilmu Komunikasi',
      'S1 Administrasi Bisnis',
      'S1 Hubungan Masyarakat',
    ],
    'Fakultas Industri Kreatif': [
      'S1 Desain Komunikasi Visual',
      'S1 Desain Interior',
      'S1 Kriya Tekstil dan Fashion',
      'S1 Desain Produk',
    ],
    'Fakultas Ilmu Terapan': [
      'D3 Rekayasa Perangkat Lunak Aplikasi',
      'D3 Sistem Informasi',
      'D3 Teknologi Telekomunikasi',
    ],
  };

  bool _isValidEmail(String email) {
    return email.isNotEmpty &&
        email.endsWith('@student.telkomuniversity.ac.id');
  }

  Future<void> _requestOtp() async {
    if (!_isValidEmail(_emailController.text)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gunakan email @student.telkomuniversity.ac.id'),
        ),
      );
      return;
    }
    setState(() => _isSendingOtp = true);
    final result = await AuthService().sendOtp(_emailController.text);
    setState(() => _isSendingOtp = false);
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(result['message'])));
  }

  Future<void> _submitRegister() async {
    if (!_isValidEmail(_emailController.text)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gunakan email @student.telkomuniversity.ac.id'),
        ),
      );
      return;
    }
    if (_selectedFakultas == null || _selectedProdi == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih Fakultas dan Program Studi')),
      );
      return;
    }
    if (_passwordController.text != _confirmPasswordController.text) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Password tidak cocok')));
      return;
    }
    if (_otpController.text.length != 6) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Masukkan 6 digit OTP')));
      return;
    }

    setState(() => _isLoading = true);

    final result = await AuthService().register({
      'name': _nameController.text,
      'email': _emailController.text,
      'nim': _nimController.text,
      'fakultas': _selectedFakultas,
      'program_studi': _selectedProdi,
      'password': _passwordController.text,
      'otp': _otpController.text,
    });

    setState(() => _isLoading = false);
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(result['message'])));

    if (result['success']) {
      Navigator.pop(context);
    }
  }

  Widget _buildInput(
    String hint,
    IconData icon,
    TextEditingController controller, {
    TextInputType type = TextInputType.text,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.whiteColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: TextField(
        controller: controller,
        keyboardType: type,
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: Icon(icon, color: AppColors.greyTextColor),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 16,
          ),
        ),
      ),
    );
  }

  Widget _buildPasswordInput(
    String hint,
    TextEditingController controller,
    bool isObscure,
    VoidCallback toggleObscure,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.whiteColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: TextField(
        controller: controller,
        obscureText: isObscure,
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: const Icon(Icons.lock, color: AppColors.greyTextColor),
          suffixIcon: IconButton(
            icon: Icon(
              isObscure ? Icons.visibility_off : Icons.visibility,
              color: AppColors.greyTextColor,
            ),
            onPressed: toggleObscure,
          ),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 16,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundColor,
      appBar: AppBar(
        backgroundColor: AppColors.backgroundColor,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.blackTextColor),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "Buat Akun",
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: AppColors.primaryColor,
              ),
            ),
            const SizedBox(height: 24),

            _buildInput("Nama Lengkap", Icons.person, _nameController),
            _buildInput(
              "NIM",
              Icons.badge,
              _nimController,
              type: TextInputType.number,
            ),

            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: _buildInput(
                    "Email Kampus",
                    Icons.email,
                    _emailController,
                    type: TextInputType.emailAddress,
                  ),
                ),
                const SizedBox(width: 8),
                SizedBox(
                  height: 55,
                  child: ElevatedButton(
                    onPressed: _isSendingOtp ? null : _requestOtp,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primaryColor,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isSendingOtp
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                        : const Text(
                            "Kirim OTP",
                            style: TextStyle(color: Colors.white),
                          ),
                  ),
                ),
              ],
            ),

            _buildInput(
              "Masukkan 6 Digit OTP",
              Icons.message,
              _otpController,
              type: TextInputType.number,
            ),

            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.whiteColor,
                borderRadius: BorderRadius.circular(12),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  isExpanded: true,
                  hint: const Text(
                    "Pilih Fakultas",
                    style: TextStyle(color: AppColors.greyTextColor),
                  ),
                  value: _selectedFakultas,
                  items: _fakultasOptions
                      .map((v) => DropdownMenuItem(value: v, child: Text(v)))
                      .toList(),
                  onChanged: (v) => setState(() {
                    _selectedFakultas = v;
                    _selectedProdi = null;
                  }),
                ),
              ),
            ),
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.whiteColor,
                borderRadius: BorderRadius.circular(12),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  isExpanded: true,
                  hint: const Text(
                    "Pilih Program Studi",
                    style: TextStyle(color: AppColors.greyTextColor),
                  ),
                  value: _selectedProdi,
                  items: _selectedFakultas == null
                      ? []
                      : _prodiOptions[_selectedFakultas]!
                            .map(
                              (v) => DropdownMenuItem(value: v, child: Text(v)),
                            )
                            .toList(),
                  onChanged: (v) => setState(() => _selectedProdi = v),
                ),
              ),
            ),

            _buildPasswordInput(
              "Password",
              _passwordController,
              _obscurePassword,
              () => setState(() => _obscurePassword = !_obscurePassword),
            ),
            _buildPasswordInput(
              "Konfirmasi Password",
              _confirmPasswordController,
              _obscureConfirmPassword,
              () => setState(
                () => _obscureConfirmPassword = !_obscureConfirmPassword,
              ),
            ),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _submitRegister,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primaryColor,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text(
                        "Daftar Sekarang",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
