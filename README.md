# SymfoX - European Social Network Platform

[![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=for-the-badge&logo=symfony)](https://symfony.com/)
[![React](https://img.shields.io/badge/React-18.2-61DAFB?style=for-the-badge&logo=react)](https://reactjs.org/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)](https://php.net/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0-3178C6?style=for-the-badge&logo=typescript)](https://www.typescriptlang.org/)
[![Doctrine](https://img.shields.io/badge/Doctrine-ORM-000000?style=for-the-badge&logo=doctrine)](https://www.doctrine-project.org/)
[![API Platform](https://img.shields.io/badge/API_Platform-3.2-6A4C93?style=for-the-badge&logo=api-platform)](https://api-platform.com/)

## 🌟 Overview

**SymfoX** is a modern, GDPR-compliant social network platform designed as an ethical alternative to traditional social media. Built with Symfony 7.3 and React, it provides a secure, transparent, and user-friendly environment for content sharing and social interaction.

### 🎯 Key Features

- **🔐 Secure Authentication** - JWT-based authentication with role-based access control
- **📱 Modern UI/UX** - React frontend with glassmorphism design and responsive layout
- **📊 Content Management** - Publications, comments, likes, and reposts
- **🔍 Smart Search** - Advanced search with hashtag support
- **🤖 AI-Powered Suggestions** - Intelligent content recommendation algorithm
- **📈 Real-time Analytics** - User engagement tracking and insights
- **🌍 GDPR Compliant** - European data protection standards compliance

## 🏗️ Architecture

### Backend Stack
- **Framework**: Symfony 7.3
- **Database**: MySQL with Doctrine ORM
- **API**: API Platform 3.2 with OpenAPI/Swagger
- **Authentication**: JWT with LexikJWTAuthenticationBundle
- **Security**: Symfony Security Bundle with custom authenticators

### Frontend Stack
- **Framework**: React 18.2 with TypeScript
- **Styling**: CSS3 with custom design system
- **Build Tool**: Vite
- **State Management**: React Context API
- **HTTP Client**: Fetch API with custom hooks

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.0+
- Node.js 18+
- MySQL 8.0+
- Symfony CLI

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/william-rauwens-oliver/symfony-e.git
   cd symfony-e
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env .env.local
   # Edit .env.local with your database credentials
   ```

5. **Setup database**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

6. **Generate JWT keys**
   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start development server**
   ```bash
   symfony server:start
   ```

### Development

- **Backend**: `http://localhost:8000`
- **Frontend**: `http://localhost:5173` (Vite dev server)
- **API Documentation**: `http://localhost:8000/api`

## 📁 Project Structure

```
symfony-e/
├── assets/                 # Frontend assets
│   ├── react/             # React application
│   │   ├── components/    # Reusable components
│   │   ├── pages/         # Page components
│   │   ├── context/       # React contexts
│   │   └── api/           # API integration
│   └── styles/            # Global styles
├── src/
│   ├── Controller/        # Symfony controllers
│   │   └── Api/          # API controllers
│   ├── Entity/           # Doctrine entities
│   ├── Repository/       # Data repositories
│   ├── Service/          # Business logic
│   ├── Security/         # Authentication & authorization
│   ├── Form/             # Symfony forms
│   └── DataPersister/    # API Platform data persisters
├── config/               # Symfony configuration
├── migrations/           # Database migrations
├── templates/            # Twig templates
└── tests/               # Test suite
```

## 🎨 Design System

### Color Palette
- **Primary Green**: `#2d5a27` - Main brand color
- **Primary Blue**: `#1da1f2` - Twitter-inspired blue
- **Primary Pink**: `#f91880` - Accent color
- **Gradient Purple**: `#667eea` - Gradient elements

### Typography
- **Font Family**: `-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif`
- **Heading 1**: 32px, weight 800
- **Heading 2**: 24px, weight 700
- **Body Text**: 16px, weight 400

### Components
- **Glassmorphism Cards** - Semi-transparent with blur effects
- **Rounded Corners** - 16px border radius for cards
- **Drop Shadows** - Subtle shadows for depth
- **Responsive Design** - Mobile-first approach

## 🔧 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout

### Publications
- `GET /api/publications` - List publications
- `POST /api/publications` - Create publication
- `GET /api/publications/{id}` - Get publication
- `PUT /api/publications/{id}` - Update publication
- `DELETE /api/publications/{id}` - Delete publication

### Comments
- `GET /api/commentaires` - List comments
- `POST /api/commentaires` - Create comment
- `DELETE /api/commentaires/{id}` - Delete comment

### User Management
- `GET /api/users/{id}` - Get user profile
- `PUT /api/users/{id}` - Update user profile
- `GET /api/users/{id}/publications` - User publications

### Search & Suggestions
- `GET /api/search` - Search publications and users
- `GET /api/suggestions` - Get content suggestions

## 🤖 Suggestion Algorithm

The platform features an intelligent content recommendation system based on:

- **User Engagement**: +5 points per like received
- **Social Validation**: +10 points for reposts by followed users
- **Discussion Quality**: +3 points per comment received
- **Hashtag Relevance**: +7 points for hashtag matches
- **Reciprocal Interaction**: +15 points for recent interactions

## 🧪 Testing

### Run Tests
```bash
# Unit tests
php bin/phpunit

# Functional tests
php bin/phpunit --testsuite=functional

# API tests
php bin/phpunit --testsuite=api
```

### Test Coverage
- **Entity Tests**: User, Publication, Comment validation
- **Controller Tests**: API endpoint functionality
- **Service Tests**: Business logic validation
- **Integration Tests**: End-to-end workflows

## 🔒 Security Features

- **JWT Authentication** - Secure token-based authentication
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive data validation
- **SQL Injection Prevention** - Doctrine ORM protection
- **XSS Protection** - Output escaping and sanitization
- **Rate Limiting** - API request throttling

## 📊 Performance

- **Database Optimization** - Indexed queries and efficient relationships
- **Caching Strategy** - Redis integration for session and data caching
- **Asset Optimization** - Minified CSS/JS with Vite
- **Image Optimization** - Compressed uploads and lazy loading
- **API Response Caching** - HTTP caching headers

## 🌍 GDPR Compliance

- **Data Minimization** - Only necessary data collection
- **User Consent** - Explicit consent for data processing
- **Right to Erasure** - Complete account deletion
- **Data Portability** - Export user data
- **Transparency** - Clear privacy policy and data usage

## 🚀 Deployment

### Production Setup
1. **Environment Configuration**
   ```bash
   APP_ENV=prod
   APP_DEBUG=false
   ```

2. **Database Migration**
   ```bash
   php bin/console doctrine:migrations:migrate --env=prod
   ```

3. **Asset Compilation**
   ```bash
   npm run build
   ```

4. **Cache Warmup**
   ```bash
   php bin/console cache:warmup --env=prod
   ```

### Docker Support
```bash
# Build and run with Docker Compose
docker-compose up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation
- Use conventional commit messages

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **William Rauwens-Oliver** - Full Stack Developer & Project Lead
- **Chaima** - Backend Development & Database Architecture
- **William & Chaima** - Collaborative development of SymfoX social network platform

## 📞 Support

- **Documentation**: [Wiki](https://github.com/william-rauwens-oliver/symfony-e/wiki)
- **Issues**: [GitHub Issues](https://github.com/william-rauwens-oliver/symfony-e/issues)
- **Discussions**: [GitHub Discussions](https://github.com/william-rauwens-oliver/symfony-e/discussions)

## 🎉 Acknowledgments

- **Symfony Team** - For the amazing framework
- **React Team** - For the powerful frontend library
- **API Platform** - For the excellent API development tools
- **European Union** - For GDPR guidelines and digital sovereignty vision

---

**Built with ❤️ for European digital sovereignty** 