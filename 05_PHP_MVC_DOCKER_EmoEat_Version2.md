# EmoEat — PHP 8.2 MVC + Docker + AI (OpenAI / Ollama) — Documentation Projet

> Application web de bien-être alimentaire émotionnel.  
> Architecture **MVC PHP 8.2** conteneurisée avec **Docker**, recommandations par **similarité cosinus**, quiz générés par **LLM** (OpenAI GPT / Ollama), gamification complète, MCP tools, API REST, et i18n EN/FR/AR (RTL).

---

## Architecture Diagram

![EmoEat Architecture](emoeat-php/docs/emoeat_architecture.png)

> Diagram generated with [diagrams (mingrammer)](https://diagrams.mingrammer.com/) — source: `emoeat_architecture.py`

---

## Table des matières

1. [Stack technique](#1-stack-technique)
2. [Architecture Docker](#2-architecture-docker)
3. [Structure du projet](#3-structure-du-projet)
4. [Schéma de base de données](#4-schéma-de-base-de-données)
5. [Design Patterns](#5-design-patterns)
6. [Core MVC Framework](#6-core-mvc-framework)
7. [Routes (23)](#7-routes-23)
8. [Contrôleurs](#8-contrôleurs)
9. [Modèles (Active Record)](#9-modèles-active-record)
10. [Services métier](#10-services-métier)
11. [Intégration LLM (OpenAI + Ollama)](#11-intégration-llm-openai--ollama)
12. [MCP — Model Context Protocol](#12-mcp--model-context-protocol)
13. [Système de gamification](#13-système-de-gamification)
14. [Internationalisation (i18n)](#14-internationalisation-i18n)
15. [Sécurité](#15-sécurité)
16. [API REST](#16-api-rest)
17. [Installation & Lancement](#17-installation--lancement)

---

## 1) Stack technique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| Langage | PHP | 8.2 |
| Serveur web | Apache | 2.4 (mod_rewrite) |
| Base de données | MySQL | 8.0 |
| Conteneurisation | Docker + Docker Compose | — |
| LLM (défaut) | OpenAI API | gpt-3.5-turbo |
| LLM (fallback) | Ollama (local) | tinyllama |
| Frontend | HTML5 + CSS3 + JS vanilla | — |
| i18n | EN / FR / AR (RTL) | 3 langues |

---

## 2) Architecture Docker

### docker-compose.yml

```yaml
services:
  php:
    build: .
    container_name: emoeat-php
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      mysql:
        condition: service_healthy
    environment:
      - DB_HOST=mysql
      - DB_NAME=emoeat
      - DB_USER=emoeat_user
      - DB_PASS=emoeat_pass
      - LLM_PROVIDER=openai
      - OPENAI_API_KEY=${OPENAI_API_KEY}
      - OLLAMA_HOST=http://host.docker.internal:11434
    networks:
      - emoeat-net

  mysql:
    image: mysql:8.0
    container_name: emoeat-mysql
    ports:
      - "3307:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root_pass
      MYSQL_DATABASE: emoeat
      MYSQL_USER: emoeat_user
      MYSQL_PASSWORD: emoeat_pass
    volumes:
      - mysql_data:/var/lib/mysql
      - ./mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 10s
      retries: 12
      start_period: 60s
    networks:
      - emoeat-net

volumes:
  mysql_data:

networks:
  emoeat-net:
```

### Dockerfile

```dockerfile
FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update && apt-get install -y libcurl4-openssl-dev unzip git \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# DocumentRoot → public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/apache2.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ \
    s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader || true
COPY . .
RUN chown -R www-data:www-data /var/www/html
```

### Réseau

| Service | Port hôte | Port conteneur | Rôle |
|---------|-----------|----------------|------|
| `emoeat-php` | 8080 | 80 | Serveur web PHP + Apache |
| `emoeat-mysql` | 3307 | 3306 | Base de données MySQL 8.0 |

- **Réseau** : `emoeat-net` (bridge Docker)
- **Volume** : `mysql_data` (persistance BDD)

---

## 3) Structure du projet

```
emoeat-php/
├── docker-compose.yml
├── Dockerfile
├── composer.json
├── .env / .env.example
│
├── mysql/
│   └── init.sql                          # Schéma + données initiales
│
├── public/                               # DocumentRoot Apache
│   ├── index.php                         # Front Controller
│   ├── .htaccess                         # Réécriture URL
│   └── assets/
│       ├── css/style.css                 # Design system complet
│       └── js/app.js                     # Interactivité client
│
├── app/
│   ├── Config/config.php                 # Configuration centralisée
│   │
│   ├── Core/                             # Framework MVC
│   │   ├── App.php                       # Singleton + Front Controller
│   │   ├── Router.php                    # Routage avec {param}
│   │   ├── Controller.php                # Contrôleur abstrait
│   │   ├── Model.php                     # ORM Active Record
│   │   ├── Database.php                  # Singleton PDO
│   │   ├── Session.php                   # Session + CSRF
│   │   ├── I18n.php                      # Internationalisation
│   │   └── Middleware/AuthMiddleware.php
│   │
│   ├── Models/                           # 9 entités Active Record
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
│   ├── Controllers/                      # 8 contrôleurs
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── EmotionController.php
│   │   ├── ProfileController.php
│   │   ├── QuizController.php
│   │   ├── RecommendationController.php
│   │   ├── GamificationController.php
│   │   ├── LanguageController.php
│   │   └── ApiController.php
│   │
│   ├── Services/                         # Logique métier
│   │   ├── LLMService.php                # Adaptateur OpenAI / Ollama
│   │   ├── QuizGeneratorService.php      # Génération de quiz IA
│   │   ├── RecommendationService.php     # Similarité cosinus
│   │   ├── GamificationService.php       # Badges et XP
│   │   └── MCPService.php                # MCP Protocol
│   │
│   ├── Lang/                             # Traductions
│   │   ├── en.php                        # Anglais (120+ clés)
│   │   ├── fr.php                        # Français
│   │   └── ar.php                        # Arabe (RTL)
│   │
│   └── Views/                            # 13 templates
│       ├── layouts/main.php              # Layout principal + RTL
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
│
└── docs/
    └── emoeat_architecture.png           # Diagramme d'architecture
```

---

## 4) Schéma de base de données

### Diagramme entité-relation

```
users ─────────┬──── emotion_logs ──── emotions
               │          │
               │          └──── recommendations ──── recipes
               │
               ├──── quizzes
               │        └──── quiz_results
               │
               └──── user_badges ──── badges
```

### Tables SQL

```sql
-- Utilisateurs (auth + gamification)
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) UNIQUE NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,        -- bcrypt
    avatar_url      VARCHAR(500),
    bio             TEXT,
    xp              INT DEFAULT 0,
    level           INT DEFAULT 1,
    streak_days     INT DEFAULT 0,
    last_login      DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Catalogue d'émotions (10 prédéfinies)
CREATE TABLE emotions (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    code  VARCHAR(30) UNIQUE NOT NULL,    -- happy, sad, angry, anxious...
    label VARCHAR(50) NOT NULL,
    emoji VARCHAR(10)
);

-- Journal des check-ins d'humeur
CREATE TABLE emotion_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    emotion_id  INT NOT NULL,
    intensity   INT DEFAULT 5,            -- 1 à 10
    context     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (emotion_id) REFERENCES emotions(id)
);

-- Catalogue de recettes (10 prédéfinies, avec tags)
CREATE TABLE recipes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    ingredients TEXT,
    prep_time   INT,                      -- minutes
    calories    INT,
    image_url   VARCHAR(500),
    tags        VARCHAR(500),             -- comma-separated
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recommandations (mood → recette, score cosinus)
CREATE TABLE recommendations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    emotion_log_id  INT NOT NULL,
    recipe_id       INT NOT NULL,
    score           INT DEFAULT 0,        -- 0-100%
    justification   TEXT,                 -- Généré par GPT
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emotion_log_id) REFERENCES emotion_logs(id),
    FOREIGN KEY (recipe_id)      REFERENCES recipes(id)
);

-- Quiz générés par LLM
CREATE TABLE quizzes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    title           VARCHAR(200),
    mood_context    VARCHAR(50),
    llm_provider    VARCHAR(20) DEFAULT 'openai',
    questions_json  LONGTEXT,             -- JSON array
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Résultats de quiz
CREATE TABLE quiz_results (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id      INT NOT NULL,
    user_id      INT NOT NULL,
    answers_json LONGTEXT,
    score        INT DEFAULT 0,
    max_score    INT DEFAULT 0,
    xp_earned    INT DEFAULT 0,
    feedback     TEXT,                    -- Feedback LLM
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Badges (10 prédéfinis)
CREATE TABLE badges (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50) UNIQUE NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    icon        VARCHAR(10),
    xp_reward   INT DEFAULT 0,
    criteria    VARCHAR(200)              -- ex: "quizzes_completed>=10"
);

-- Table de jonction utilisateur ↔ badge
CREATE TABLE user_badges (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL,
    badge_id  INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, badge_id),
    FOREIGN KEY (user_id)  REFERENCES users(id),
    FOREIGN KEY (badge_id) REFERENCES badges(id)
);
```

### Données initiales

- **10 émotions** : happy, sad, angry, anxious, tired, stressed, excited, calm, lonely, bored
- **10 recettes** : Banana Oat Smoothie, Dark Chocolate Mousse, Salmon Poke Bowl, etc. (taggées)
- **10 badges** : Welcome!, Mood Starter, Quiz Rookie, Quiz Master, 3-Day Streak, Week Warrior, etc.
- **1 utilisateur admin** : `admin` / `admin@emoeat.local` / `admin123` (bcrypt)

---

## 5) Design Patterns

| Pattern | Classe(s) | Description |
|---------|-----------|-------------|
| **Singleton** | `App`, `Database`, `I18n` | Instance unique avec constructeur privé et `getInstance()` |
| **Front Controller** | `public/index.php` + `App` | Point d'entrée unique, routage centralisé |
| **Strategy** | `LLMService` | Sélection d'algorithme à l'exécution (OpenAI vs Ollama) |
| **Active Record** | `Model` + 9 entités | Objets mappés aux lignes BDD avec CRUD intégré |
| **Template Method** | `Controller` (base) | Méthodes `view()`, `json()`, `redirect()` héritées |
| **Observer** | `GamificationService` | Vérification de badges déclenchée après actions utilisateur |
| **Adapter** | `LLMService` | Interface unifiée pour backends LLM multiples |

### Diagramme des patterns

```
┌─────────────────────────────────────────────────────────┐
│                  SINGLETON PATTERN                       │
│  App::getInstance()  Database::getInstance()  I18n::..  │
└─────────────────────────────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        ▼                  ▼                  ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────────┐
│ FRONT        │  │ TEMPLATE     │  │ STRATEGY PATTERN │
│ CONTROLLER   │  │ METHOD       │  │                  │
│              │  │              │  │ LLMService       │
│ index.php    │  │ Controller   │  │  ├─ callOpenAI() │
│  → Router    │  │  ├─ view()   │  │  └─ callOllama() │
│  → dispatch  │  │  ├─ json()   │  │                  │
│              │  │  └─ redirect │  │ Provider chosen  │
│              │  │              │  │ at runtime       │
└──────────────┘  └──────────────┘  └──────────────────┘
                           │
                           ▼
            ┌──────────────────────────┐
            │   ACTIVE RECORD PATTERN  │
            │                          │
            │ Model::find($id)         │
            │ Model::where($col, $val) │
            │ $model->save()           │
            │ $model->delete()         │
            │                          │
            │ 9 entités : User,        │
            │ Emotion, EmotionLog,     │
            │ Recipe, Quiz, Badge...   │
            └──────────────────────────┘
```

---

## 6) Core MVC Framework

### Front Controller (`public/index.php`)

```php
define('BASE_PATH', dirname(__DIR__));

// PSR-4 autoloader
spl_autoload_register(function (string $class) {
    $prefix  = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

$config = require BASE_PATH . '/app/Config/config.php';
App::getInstance($config)->run();
```

### Router (`App\Core\Router`)

- Support `{param}` dans les chemins (ex: `/quiz/take/{id}`)
- Correspondance par méthode HTTP (GET/POST)
- Extraction des paramètres nommés par regex

### Controller de base (`App\Core\Controller`)

```php
abstract class Controller
{
    protected function view(string $viewName, array $data = []): void
    {
        extract($data);
        ob_start();
        require BASE_PATH . "/app/Views/{$viewName}.php";
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/layouts/main.php';
    }

    protected function json(mixed $data, int $status = 200): void;
    protected function redirect(string $url): void;
    protected function requireAuth(): void;
    protected function db(): PDO;
}
```

### Active Record ORM (`App\Core\Model`)

```php
class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?static;
    public static function findBy(string $column, mixed $value): ?static;
    public static function where(string $column, mixed $value): array;
    public static function all(): array;
    public static function query(string $sql, array $params = []): array;
    public function save(): bool;     // INSERT or UPDATE
    public function delete(): bool;
}
```

---

## 7) Routes (23)

### Authentification (6)

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| GET | `/` | Auth | loginForm |
| GET | `/login` | Auth | loginForm |
| POST | `/login` | Auth | doLogin |
| GET | `/register` | Auth | registerForm |
| POST | `/register` | Auth | doRegister |
| GET | `/logout` | Auth | logout |

### Application (13)

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| GET | `/dashboard` | Dashboard | index |
| GET | `/profile` | Profile | index |
| POST | `/profile` | Profile | update |
| GET | `/checkin` | Emotion | form |
| POST | `/checkin` | Emotion | submit |
| GET | `/recommendations` | Recommendation | index |
| GET | `/quiz` | Quiz | index |
| POST | `/quiz/generate` | Quiz | generate |
| GET | `/quiz/take/{id}` | Quiz | take |
| POST | `/quiz/submit` | Quiz | submit |
| GET | `/quiz/result/{id}` | Quiz | result |
| GET | `/badges` | Gamification | badges |
| GET | `/leaderboard` | Gamification | leaderboard |

### i18n (1)

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| GET | `/lang/{locale}` | Language | switchLang |

### API REST (5)

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| POST | `/api/quiz/generate` | Api | generateQuiz |
| POST | `/api/recommend` | Api | recommend |
| GET | `/api/emotions` | Api | emotions |
| GET | `/api/profile` | Api | profile |
| POST | `/api/mcp/invoke` | Api | mcpInvoke |

---

## 8) Contrôleurs

### AuthController

```php
class AuthController extends Controller
{
    public function loginForm(): void;
    public function doLogin(): void     // bcrypt verify + session + streak
    {
        $user = User::findByEmail($email);
        if ($user && password_verify($password, $user->password_hash)) {
            Session::login($user->id, $user->username);
            $user->updateStreak();
            GamificationService::checkBadges($user);
        }
    }
    public function registerForm(): void;
    public function doRegister(): void;  // bcrypt hash + validation
    public function logout(): void;
}
```

### EmotionController

```php
class EmotionController extends Controller
{
    public function submit(): void
    {
        // 1. Enregistrer le check-in (EmotionLog)
        // 2. Ajouter 10 XP à l'utilisateur
        // 3. Générer recommandations (RecommendationService)
        // 4. Vérifier badges (GamificationService)
        // 5. Rediriger vers /recommendations
    }
}
```

### QuizController

```php
class QuizController extends Controller
{
    public function generate(): void
    {
        // QuizGeneratorService → LLM → JSON quiz
        // Stocké dans quizzes.questions_json
    }
    public function submit(): void
    {
        // Correction automatique + calcul score
        // XP = 25 (normal) ou 50 (score parfait)
        // Feedback LLM généré
    }
}
```

---

## 9) Modèles (Active Record)

| Modèle | Table | Méthodes spécifiques |
|--------|-------|---------------------|
| `User` | users | `findByEmail()`, `verifyPassword()`, `addXp()`, `updateStreak()` |
| `Emotion` | emotions | `allSorted()` |
| `EmotionLog` | emotion_logs | `lastByUser()`, `recentByUser()`, `uniqueEmotionCount()` |
| `Recipe` | recipes | CRUD standard |
| `Recommendation` | recommendations | `byEmotionLog()` |
| `Quiz` | quizzes | `getQuestions()`, `byUser()` |
| `QuizResult` | quiz_results | `byUser()`, `completedCount()` |
| `Badge` | badges | `earnedByUser()`, `notEarnedByUser()` |
| `UserBadge` | user_badges | `award()` |

---

## 10) Services métier

### RecommendationService — Similarité cosinus

```php
class RecommendationService
{
    // Vecteurs de poids humeur → tags
    private const MOOD_TAG_WEIGHTS = [
        'happy'    => ['sweet' => 0.8, 'light' => 0.6, 'fresh' => 0.5, ...],
        'sad'      => ['comfort' => 0.9, 'warm' => 0.8, 'chocolate' => 0.7, ...],
        'stressed' => ['calming' => 0.9, 'herbal' => 0.8, 'light' => 0.6, ...],
        // ... 10 émotions
    ];

    public function generate(EmotionLog $log): array
    {
        // 1. Construire le vocabulaire (union de tous les tags)
        // 2. buildMoodVector()   — vecteur pondéré (humeur × intensité)
        // 3. buildRecipeVector() — vecteur binaire par recette
        // 4. cosineSimilarity()  — score pour chaque recette
        // 5. Top 3 résultats
        // 6. generateAIJustifications() — GPT explique chaque choix
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = $normA = $normB = 0.0;
        foreach ($a as $i => $val) {
            $dot   += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }
        $denom = sqrt($normA) * sqrt($normB);
        return $denom > 0 ? $dot / $denom : 0.0;
    }
}
```

### QuizGeneratorService — Quiz IA

```php
class QuizGeneratorService
{
    public function generate(string $moodContext): ?Quiz
    {
        // Prompt recipe-aware → LLM génère :
        // {title, questions: [{question, options[], correct, explanation, tags[]}]}
        // Tags utilisés pour le scoring
    }

    public function generateFeedback(int $score, int $max, string $mood): string
    {
        // LLM génère feedback personnalisé (2-3 phrases)
    }
}
```

### GamificationService — Badges et XP

```php
class GamificationService
{
    public static function checkBadges(User $user): array
    {
        // Évalue les critères de chaque badge non obtenu
        // Attribue automatiquement + ajoute XP reward
    }
}
```

---

## 11) Intégration LLM (OpenAI + Ollama)

### Architecture Strategy Pattern

```
┌──────────────┐
│  LLMService  │
│  complete()  │─────┬─── callOpenAI()  ──→ api.openai.com
│              │     │                        gpt-3.5-turbo
│  provider =  │     │
│  config      │     └─── callOllama()  ──→ host.docker.internal:11434
│  (runtime)   │                              tinyllama
└──────────────┘
       ↑
       │ fallbackResponse() si tous échouent
```

### LLMService

```php
class LLMService
{
    public function complete(string $prompt, ?string $provider = null): string
    {
        $provider = $provider ?? $this->config['llm']['provider']; // 'openai'
        return match ($provider) {
            'openai' => $this->callOpenAI($prompt),
            'ollama' => $this->callOllama($prompt),
            default  => $this->fallbackResponse(),
        };
    }

    private function callOpenAI(string $prompt): string
    {
        // POST https://api.openai.com/v1/chat/completions
        // Model: gpt-3.5-turbo
        // Bearer: OPENAI_API_KEY
        // Timeout: 60s connect, 60s total
    }

    private function callOllama(string $prompt): string
    {
        // POST http://host.docker.internal:11434/api/generate
        // Model: tinyllama
        // stream: false
    }
}
```

### Configuration `.env`

```env
LLM_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-3.5-turbo
OLLAMA_HOST=http://host.docker.internal:11434
OLLAMA_MODEL=tinyllama
```

---

## 12) MCP — Model Context Protocol

### 4 Outils MCP

| Outil | Description | Paramètres |
|-------|-------------|------------|
| `generate_quiz` | Générer un quiz IA selon l'humeur | `mood`, `num_questions` |
| `recommend_food` | Recommandations alimentaires | `mood`, `intensity` |
| `mood_analysis` | Analyse de patterns d'humeur | `moods[]` |
| `nutrition_advice` | Conseils nutritionnels | `topic` |

### Invocation

```http
POST /api/mcp/invoke
Content-Type: application/json

{
    "tool": "recommend_food",
    "params": { "mood": "stressed", "intensity": 7 },
    "provider": "openai"
}
```

---

## 13) Système de gamification

### XP et niveaux

| Action | XP gagné |
|--------|----------|
| Check-in d'humeur | +10 XP |
| Quiz complété | +25 XP |
| Quiz score parfait | +50 XP |
| Badge obtenu | +XP du badge |

- **Formule niveau** : `Level = floor(XP / 100) + 1`
- **Barre de progression** : `(XP % 100) / 100 * 100%`

### Badges (10)

| Badge | Code | Icône | XP | Critère |
|-------|------|-------|-----|---------|
| Welcome! | first_login | 👋 | 10 | login_count ≥ 1 |
| Mood Starter | first_checkin | 🎯 | 20 | checkins ≥ 1 |
| Quiz Rookie | quiz_rookie | 📝 | 25 | quizzes_completed ≥ 1 |
| Quiz Master | quiz_master | 🧠 | 100 | quizzes_completed ≥ 10 |
| 3-Day Streak | streak_3 | 🔥 | 50 | streak_days ≥ 3 |
| Week Warrior | streak_7 | ⚡ | 100 | streak_days ≥ 7 |
| Mood Explorer | mood_explorer | 🌈 | 40 | unique_emotions ≥ 5 |
| Healthy Eater | healthy_eater | 🥗 | 60 | recommendations_followed ≥ 10 |
| Rising Star | level_5 | ⭐ | 50 | level ≥ 5 |
| EmoEat Champion | level_10 | 🏆 | 200 | level ≥ 10 |

### Leaderboard

- Top 20 utilisateurs, triés par XP DESC
- Affiche : rang, nom, niveau, XP, série

---

## 14) Internationalisation (i18n)

### Langues supportées

| Code | Langue | Direction | Fichier |
|------|--------|-----------|---------|
| `en` | English | LTR | `app/Lang/en.php` |
| `fr` | Français | LTR | `app/Lang/fr.php` |
| `ar` | العربية | **RTL** | `app/Lang/ar.php` |

### Architecture

```php
// I18n Singleton (App\Core\I18n)
namespace App\Core {
    class I18n {
        public const SUPPORTED = ['en', 'fr', 'ar'];
        public const RTL       = ['ar'];

        public static function setLocale(string $locale): void;
        public function getLocale(): string;
        public function isRtl(): bool;
        public function t(string $key, mixed ...$args): string;
    }
}

// Global helper (root namespace)
namespace {
    function __(string $key, mixed ...$args): string {
        return \App\Core\I18n::getInstance()->t($key, ...$args);
    }
}
```

### Utilisation dans les vues

```php
<!-- Layout: lang + direction dynamiques -->
<html lang="<?= $i18n->getLocale() ?>" dir="<?= $i18n->isRtl() ? 'rtl' : 'ltr' ?>">

<!-- Traductions avec placeholders -->
<h1><?= __('dash_welcome', $user->username) ?></h1>
<p><?= __('reco_intensity', $lastMood->intensity) ?></p>

<!-- Sélecteur de langue -->
<a href="/lang/en" class="lang-btn">EN</a>
<a href="/lang/fr" class="lang-btn">FR</a>
<a href="/lang/ar" class="lang-btn">AR</a>
```

### Support RTL (CSS)

```css
[dir="rtl"] body        { direction: rtl; text-align: right; }
[dir="rtl"] .navbar     { flex-direction: row-reverse; }
[dir="rtl"] .nav-links  { flex-direction: row-reverse; }
[dir="rtl"] .nav-user   { flex-direction: row-reverse; }
[dir="rtl"] .card       { text-align: right; }
[dir="rtl"] .form-group { text-align: right; }
```

---

## 15) Sécurité

| Protection | Implémentation | Localisation |
|------------|---------------|--------------|
| **Hachage mot de passe** | bcrypt (`PASSWORD_BCRYPT`) | AuthController, ProfileController |
| **Protection CSRF** | Token en session + vérification POST | Session::csrfToken(), verifyCsrf() |
| **Prévention injection SQL** | Requêtes préparées PDO | Model.php + tous les contrôleurs |
| **Prévention XSS** | `htmlspecialchars()` dans toutes les vues | Tous les templates |
| **Gestion de session** | Session PHP + validation user_id | Session class, requireAuth() |
| **Validation d'entrée** | Type casting + trim + filter_var | Tous les contrôleurs |
| **Authentification** | `requireAuth()` avant actions protégées | Contrôleurs protégés |
| **Unicité email/username** | Vérification pré-insertion | AuthController::doRegister |

---

## 16) API REST

### Endpoints JSON

| Méthode | Endpoint | Auth | Entrée | Sortie |
|---------|----------|------|--------|--------|
| POST | `/api/quiz/generate` | ✅ | `{mood, provider}` | `{title, questions[]}` |
| POST | `/api/recommend` | ✅ | `{mood, intensity}` | `{recommendations[]}` |
| GET | `/api/emotions` | ✅ | — | `[{id, code, label, emoji}]` |
| GET | `/api/profile` | ✅ | — | `{id, username, email, xp, level}` |
| POST | `/api/mcp/invoke` | ✅ | `{tool, params, provider}` | Réponse outil |

### Exemple d'appel

```bash
# Générer un quiz via API
curl -X POST http://localhost:8080/api/quiz/generate \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=..." \
  -d '{"mood": "stressed", "provider": "openai"}'

# Invoquer un outil MCP
curl -X POST http://localhost:8080/api/mcp/invoke \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=..." \
  -d '{"tool": "nutrition_advice", "params": {"topic": "stress eating"}}'
```

---

## 17) Installation & Lancement

### Prérequis

- Docker + Docker Compose
- (Optionnel) Clé API OpenAI pour les fonctionnalités LLM

### Démarrage rapide

```bash
# 1. Cloner le dépôt
git clone <repo-url>
cd emoeat-php

# 2. Configurer l'environnement
cp .env.example .env
# Éditer .env : ajouter OPENAI_API_KEY

# 3. Lancer les conteneurs
docker-compose up --build -d

# 4. Attendre que MySQL soit prêt (~30-60s)
docker-compose logs -f mysql   # attendre "ready for connections"

# 5. Accéder à l'application
# → http://localhost:8080

# Identifiants par défaut :
# Email    : admin@emoeat.local
# Password : admin123
```

### Commandes utiles

```bash
# Reconstruire PHP uniquement
docker-compose up --build -d php

# Voir les logs
docker logs emoeat-php --tail 50
docker logs emoeat-mysql --tail 50

# Accéder au shell PHP
docker exec -it emoeat-php bash

# Accéder à MySQL
docker exec -it emoeat-mysql mysql -u emoeat_user -p emoeat

# Arrêter tout
docker-compose down

# Arrêter + supprimer les données
docker-compose down -v
```

---

> **EmoEat** — Emotional Eating Wellness Platform  
> PHP 8.2 · MySQL 8.0 · Docker · OpenAI GPT · Ollama · MCP · Cosine Similarity · Gamification · i18n (EN/FR/AR)
