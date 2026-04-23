# EmoEat — PHP MVC Web Application

> Emotional Eating Wellness App with Docker, MySQL, ORM, Gamification, LLM Quiz (Ollama/OpenAI MCP)

---

## Architecture

```
emoeat-php/
├── docker-compose.yml          # PHP + MySQL containers
├── Dockerfile                  # PHP 8.2 Apache image
├── composer.json               # PSR-4 autoloading
├── .env.example                # Environment config template
│
├── mysql/
│   └── init.sql                # Full schema + seed data
│
├── public/                     # DocumentRoot (Apache)
│   ├── index.php               # Front Controller
│   ├── .htaccess               # URL rewriting
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
│
├── app/
│   ├── Config/
│   │   └── config.php          # App configuration
│   │
│   ├── Core/                   # MVC Framework
│   │   ├── App.php             # Singleton App + Router bootstrap
│   │   ├── Router.php          # Route matching with {params}
│   │   ├── Controller.php      # Base controller (Template Method)
│   │   ├── Model.php           # Active Record ORM base
│   │   ├── Database.php        # Singleton PDO connection
│   │   ├── Session.php         # Session + CSRF protection
│   │   └── Middleware/
│   │       └── AuthMiddleware.php
│   │
│   ├── Models/                 # ORM Entities
│   │   ├── User.php
│   │   ├── Emotion.php
│   │   ├── EmotionLog.php
│   │   ├── Recipe.php
│   │   ├── Recommendation.php
│   │   ├── Quiz.php
│   │   ├── QuizResult.php
│   │   ├── Badge.php
│   │   └── UserBadge.php
│   │
│   ├── Controllers/
│   │   ├── AuthController.php          # Login / Register / Logout
│   │   ├── DashboardController.php     # Dashboard with stats
│   │   ├── ProfileController.php       # User profile edit
│   │   ├── EmotionController.php       # Mood check-in
│   │   ├── RecommendationController.php# Food recommendations
│   │   ├── QuizController.php          # LLM-generated quizzes
│   │   ├── GamificationController.php  # Badges & Leaderboard
│   │   └── ApiController.php           # REST JSON API + MCP
│   │
│   ├── Services/               # Business Logic Layer
│   │   ├── LLMService.php              # Ollama + OpenAI adapter (Strategy)
│   │   ├── MCPService.php              # Model Context Protocol tools
│   │   ├── QuizGeneratorService.php    # Quiz generation via LLM
│   │   ├── RecommendationService.php   # Mood-food matching
│   │   └── GamificationService.php     # XP, levels, badges
│   │
│   └── Views/
│       ├── layouts/main.php            # Master layout
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── dashboard/index.php
│       ├── profile/index.php
│       ├── emotion/form.php
│       ├── recommendation/index.php
│       ├── quiz/index.php
│       ├── quiz/take.php
│       ├── quiz/result.php
│       ├── gamification/badges.php
│       └── gamification/leaderboard.php
```

---

## Quick Start

### 1. Clone and configure

```bash
cd emoeat-php
cp .env.example .env
# Edit .env if needed (OpenAI key, Ollama host, etc.)
```

### 2. Start with Docker

```bash
docker-compose up --build -d
```

This starts:
- **PHP 8.2 + Apache** on `http://localhost:8080`
- **MySQL 8.0** on `localhost:3307` (internal: `mysql:3306`)

### 3. Access the app

Open **http://localhost:8080** → Login page

Default admin account:
- Email: `admin@emoeat.local`
- Password: `admin123`

### 4. (Optional) Local LLM with Ollama

```bash
# Install Ollama: https://ollama.ai
ollama pull tinyllama
ollama serve
```

The app connects to `http://host.docker.internal:11434` by default.

### 5. (Optional) OpenAI GPT

Set in `.env`:
```
LLM_PROVIDER=openai
OPENAI_API_KEY=sk-your-key-here
```

---

## Design Patterns Used

| Pattern | Where | Purpose |
|---------|-------|---------|
| **Singleton** | `Database.php`, `App.php` | Single DB connection, single app instance |
| **Front Controller** | `public/index.php` | All requests routed through one entry point |
| **Active Record (ORM)** | `Model.php` + all Models | Objects map to DB rows with CRUD methods |
| **Template Method** | `Controller.php` | Base controller with shared helpers |
| **Strategy** | `LLMService.php` | Swap Ollama ↔ OpenAI at runtime |
| **MVC** | Full architecture | Separation of concerns |

---

## Features

### Authentication & Identity
- User registration with bcrypt password hashing
- Login/logout with session management
- CSRF token protection on all forms
- Profile editing (username, bio, avatar, password)

### Mood Check-in
- Select from 10 emotions with emoji icons
- Intensity slider (1-10)
- Optional context description
- Triggers food recommendations

### Food Recommendations
- Mood-to-food tag matching algorithm
- Top 5 recipes scored by relevance
- Recipe details (ingredients, prep time, calories)

### AI-Powered Quiz (LLM / MCP)
- Generate quizzes via **Ollama** (local TinyLlama) or **OpenAI GPT**
- Multiple-choice questions about emotional eating & nutrition
- Automatic scoring with XP rewards
- AI-generated feedback on results
- Fallback static quiz when LLM is unavailable

### MCP (Model Context Protocol)
- `POST /api/mcp/invoke` — unified tool invocation
- Available tools:
  - `generate_quiz` — quiz generation
  - `recommend_food` — mood-based food suggestions
  - `mood_analysis` — pattern analysis
  - `nutrition_advice` — topic-based advice
- Provider switchable: `ollama` or `openai`

### Gamification
- XP system (earn XP for check-ins, quizzes)
- Level progression (100 XP per level)
- Login streak tracking
- 10 achievement badges
- Leaderboard

### REST API
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/emotions` | List all emotions |
| GET | `/api/profile` | Current user profile |
| POST | `/api/quiz/generate` | Generate quiz via LLM |
| POST | `/api/recommend` | Get food recommendations |
| POST | `/api/mcp/invoke` | MCP tool invocation |

---

## Database Schema (MySQL)

Tables: `users`, `emotions`, `emotion_logs`, `recipes`, `recommendations`, `quizzes`, `quiz_results`, `badges`, `user_badges`

See `mysql/init.sql` for full schema.

---

## Stop & Cleanup

```bash
docker-compose down          # Stop containers
docker-compose down -v       # Stop + remove data volumes
```
# EmoEat
