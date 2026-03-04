<p align="center">
  <img src="https://img.shields.io/badge/Symfony-6.4-000000?style=for-the-badge&logo=symfony&logoColor=white" alt="Symfony"/>
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Stripe-008CDD?style=for-the-badge&logo=stripe&logoColor=white" alt="Stripe"/>
  <img src="https://img.shields.io/badge/Google_AI-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Google AI"/>
  <img src="https://img.shields.io/badge/Twilio-F22F46?style=for-the-badge&logo=twilio&logoColor=white" alt="Twilio"/>
</p>

# 🩸 BloodLink — Intelligent Blood Donation Management Platform

> **A comprehensive web application for managing the full blood donation lifecycle — from donor registration and eligibility assessment to blood bank stock management and AI-powered analytics.**

---

## 📋 Overview

This project was developed as part of the **PIDEV – 3rd Year Engineering Program at Esprit School of Engineering** (Academic Year **2025–2026**).

**BloodLink** is a full-stack web platform that digitalizes and streamlines the blood donation process for donors, medical staff, and administrators.

The platform connects **donors**, **blood banks**, and **collection entities** through a unified interface that handles:

- Donor registration and authentication (including Google OAuth and facial recognition)
- Medical dossier management with AI-powered eligibility assessment
- Appointment scheduling with SMS notifications and Google Calendar integration
- Blood donation campaigns with AI-based donor turnout prediction
- Blood stock and inter-bank transfer management
- Order processing with Stripe payment integration
- AI chatbot for real-time donor assistance
- Comprehensive admin dashboard with AI-generated analytical reports

---

## ✨ Features

### 👤 User Management & Authentication
- User registration with email verification (`SymfonyCasts VerifyEmailBundle`)
- Secure login with Symfony Security component
- **Google OAuth 2.0** sign-in (`KnpUOAuth2ClientBundle` + `league/oauth2-google`)
- **Facial recognition login** via a Python Flask microservice (`face_recognition` library)
- Password reset functionality with token-based email recovery
- Role-based access control: `ROLE_ADMIN`, `ROLE_CLIENT`, `ROLE_DOCTOR`

### 🏥 Medical Dossiers (`DossierMed`)
- Create and manage patient medical records (age, weight, height, blood type, medical history)
- BMI calculation and biometric tracking
- **AI-powered donor retention prediction** using K-Nearest Neighbors (Rubix ML)

### 🩸 Blood Donations (`Don`)
- Record and track blood, platelet, and plasma donations
- **AI-powered donation eligibility assessment** via Google Gemini API
- Gender-aware cooldown period calculation (whole blood / platelets / plasma)

### 📅 Appointment Scheduling (`RendezVous`)
- Book, update, and cancel donation appointments
- **SMS confirmation notifications** via Twilio
- **Google Calendar integration** — automatically add appointments to the donor's calendar
- Filtering, pagination, and back-office management
- **AI-generated reports** on appointment statistics (Gemini API)
- Excel export of appointment data (`PhpSpreadsheet`)

### 📢 Donation Campaigns (`Compagne`)
- Create and manage blood donation campaigns
- **AI-powered donor prediction** using historical campaign data (Gemini API)
- Dynamic chart generation via QuickChart API

### 📝 Health Questionnaire (`Questionnaire`)
- Pre-donation health screening questionnaires linked to campaigns and clients
- Front-office submission and back-office review
- Filtering, pagination, and detailed views
- Excel export of questionnaire data

### 📦 Orders & Payments (`Commande`)
- Blood product ordering system
- **Stripe Checkout** integration for secure online payments
- Automated stock allocation upon successful payment
- Email confirmation via Symfony Mailer (Gmail SMTP)
- **PDF invoice generation** (`DomPDF`)
- Order validation workflow

### 🏦 Blood Banks & Collection Entities
- **Blood Banks (`Banque`)**: manage profiles and stock
- **Collection Entities (`EntiteCollecte`)**: manage collection centers
- **Stock Management (`Stock`)**: inventory per bank
- **Transfers (`Transfert`)**: inter-bank transfers with status tracking

### 🤖 AI Chatbot
- **Gemini-powered conversational assistant** embedded in the front-office
- Can query real database data (stocks, orders, blood bank availability)

### 📊 Admin Dashboard
- **EasyAdmin 4** back-office for comprehensive data management
- AI-generated analytical reports (executive summary, stock analysis, action plans)
- CRUD operations for all entities
- Data export capabilities

### 🔐 Demand Management (`Demande`)
- Blood product demand requests with urgency levels
- Front-office submission and back-office processing workflow

---

## 🛠 Tech Stack

### Frontend
| Technology | Purpose |
|---|---|
| **Twig** | Symfony templating engine |
| **HTML5 / CSS3 / JavaScript** | Core web technologies |
| **Bootstrap 5** | UI framework |
| **Choices.js** | Enhanced select dropdowns |
| **Symfony UX Turbo** | Partial page updates without full reloads |
| **Symfony Stimulus** | JavaScript controllers |
| **Symfony AssetMapper / Importmap** | Asset management (no Node.js required for base setup) |

### Backend
| Technology | Purpose |
|---|---|
| **Symfony 6.4** | PHP framework |
| **PHP 8.1+** | Server-side language |
| **Doctrine ORM** | Database abstraction and migrations |
| **MySQL / MariaDB** | Relational database |
| **EasyAdmin 4** | Admin panel |
| **Symfony Security** | Authentication & authorization |
| **Symfony Mailer** | Email sending |
| **Symfony Notifier + Twilio** | SMS notifications |
| **KnpPaginatorBundle** | Pagination |

### AI & Machine Learning
| Technology | Purpose |
|---|---|
| **Google Gemini API** | Chatbot, recommendations, prediction, report generation |
| **Rubix ML** | KNN classifier for donor retention prediction |

### External Services & APIs
| Service | Purpose |
|---|---|
| **Stripe** | Payment processing |
| **Twilio** | SMS notifications |
| **Google OAuth 2.0** | Social authentication |
| **Google Calendar API** | Appointment calendar sync |
| **QuickChart** | Dynamic chart generation |

### PDF & Data Export
| Technology | Purpose |
|---|---|
| **DomPDF** | PDF generation |
| **PhpSpreadsheet** | Excel/CSV export |

### Face Recognition Microservice
| Technology | Purpose |
|---|---|
| **Python / Flask** | Microservice HTTP server |
| **face_recognition** | Facial encoding and comparison |
| **flask-cors** | Cross-origin requests |


