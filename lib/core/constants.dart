import 'package:flutter/material.dart';

class AppColors {
  static const Color primaryColor = Color(0xFF0d6efd);
  static const Color backgroundColor = Color(0xFFf4f6f9);
  static const Color whiteColor = Color(0xFFffffff);
  static const Color blackTextColor = Color(0xFF212529);
  static const Color greyTextColor = Color(0xFF6c757d);
}

class ApiConstants {
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String login = '$baseUrl/login';
  static const String register = '$baseUrl/register-student';
}
