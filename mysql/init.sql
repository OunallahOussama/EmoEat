-- =============================================================
-- EmoEat MySQL Schema — Version 2
-- =============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ---- Users ----
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url  VARCHAR(255) DEFAULT NULL,
    bio         TEXT         DEFAULT NULL,
    xp          INT          NOT NULL DEFAULT 0,
    level       INT          NOT NULL DEFAULT 1,
    streak_days INT          NOT NULL DEFAULT 0,
    last_login  DATETIME     DEFAULT NULL,
    is_admin    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Emotions Catalog ----
CREATE TABLE IF NOT EXISTS emotions (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    code    VARCHAR(30)  NOT NULL UNIQUE,
    label   VARCHAR(60)  NOT NULL,
    emoji   VARCHAR(10)  DEFAULT '😐'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO emotions (code, label, emoji) VALUES
    ('happy',     'Happy',     '😊'),
    ('sad',       'Sad',       '😢'),
    ('angry',     'Angry',     '😠'),
    ('anxious',   'Anxious',   '😰'),
    ('tired',     'Tired',     '😴'),
    ('stressed',  'Stressed',  '😫'),
    ('excited',   'Excited',   '🤩'),
    ('calm',      'Calm',      '😌'),
    ('lonely',    'Lonely',    '😞'),
    ('bored',     'Bored',     '😑');

-- ---- Emotion Logs (mood check-ins) ----
CREATE TABLE IF NOT EXISTS emotion_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT      NOT NULL,
    emotion_id  INT      NOT NULL,
    intensity   TINYINT  NOT NULL DEFAULT 5 CHECK (intensity BETWEEN 1 AND 10),
    context     TEXT     DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (emotion_id) REFERENCES emotions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Recipes Catalog ----
CREATE TABLE IF NOT EXISTS recipes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    ingredients TEXT,
    prep_time   INT          DEFAULT 0 COMMENT 'minutes',
    calories    INT          DEFAULT 0,
    image_url   VARCHAR(255) DEFAULT NULL,
    tags        VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO recipes (title, description, ingredients, prep_time, calories, tags) VALUES
    ('Banana Oat Smoothie',      'A comforting smoothie to lift your mood.',         'banana, oats, milk, honey',        5,  280, 'comfort,quick,healthy'),
    ('Dark Chocolate Avocado Mousse','Rich in magnesium to reduce stress.',          'avocado, cocoa, maple syrup',     10,  320, 'stress-relief,dessert'),
    ('Salmon Poke Bowl',          'Omega-3 rich bowl for brain health.',             'salmon, rice, avocado, soy sauce', 20, 450, 'brain-food,omega3'),
    ('Chamomile Honey Tea',       'Calming drink for anxious moments.',              'chamomile, honey, lemon',          5,   40, 'calming,drink'),
    ('Spicy Thai Noodles',        'Energizing dish with a kick.',                    'noodles, chili, peanuts, lime',   15,  380, 'energy,spicy'),
    ('Berry Yogurt Parfait',      'Antioxidant-rich layers of goodness.',            'berries, yogurt, granola, honey', 10,  250, 'antioxidant,breakfast'),
    ('Green Power Salad',         'Fresh and revitalizing salad.',                   'spinach, kale, apple, walnuts',   10,  200, 'fresh,energy'),
    ('Warm Lentil Soup',          'Hearty comfort food for lonely evenings.',        'lentils, carrots, cumin, garlic', 30,  300, 'comfort,warm'),
    ('Matcha Latte',              'Focused calm energy.',                            'matcha, milk, vanilla',            5,  120, 'focus,calm'),
    ('Grilled Chicken Wrap',      'Balanced protein meal for stability.',            'chicken, lettuce, tomato, wrap',  15,  400, 'protein,balanced');

-- ---- Recommendations ----
CREATE TABLE IF NOT EXISTS recommendations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    emotion_log_id  INT     NOT NULL,
    recipe_id       INT     NOT NULL,
    score           DECIMAL(5,2) DEFAULT 0,
    justification   TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emotion_log_id) REFERENCES emotion_logs(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id)      REFERENCES recipes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Quizzes ----
CREATE TABLE IF NOT EXISTS quizzes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    title       VARCHAR(200) NOT NULL,
    mood_context VARCHAR(60) DEFAULT NULL,
    llm_provider VARCHAR(20) DEFAULT 'ollama',
    questions_json LONGTEXT  NOT NULL COMMENT 'JSON array of quiz questions',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Quiz Results ----
CREATE TABLE IF NOT EXISTS quiz_results (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id     INT      NOT NULL,
    user_id     INT      NOT NULL,
    answers_json LONGTEXT NOT NULL COMMENT 'JSON user answers',
    score       INT      NOT NULL DEFAULT 0,
    max_score   INT      NOT NULL DEFAULT 0,
    xp_earned   INT      NOT NULL DEFAULT 0,
    feedback    TEXT     DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Badges (Gamification) ----
CREATE TABLE IF NOT EXISTS badges (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50)  NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    icon        VARCHAR(10)  DEFAULT '🏅',
    xp_reward   INT          NOT NULL DEFAULT 10,
    criteria    VARCHAR(255) DEFAULT NULL COMMENT 'e.g. quizzes_completed>=5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO badges (code, name, description, icon, xp_reward, criteria) VALUES
    ('first_login',    'Welcome!',         'Logged in for the first time',              '👋', 10,  'login_count>=1'),
    ('first_checkin',  'Mood Starter',     'Completed first mood check-in',             '🎯', 20,  'checkins>=1'),
    ('quiz_rookie',    'Quiz Rookie',      'Completed first quiz',                      '📝', 25,  'quizzes_completed>=1'),
    ('quiz_master',    'Quiz Master',      'Completed 10 quizzes',                      '🧠', 100, 'quizzes_completed>=10'),
    ('streak_3',       '3-Day Streak',     'Logged in 3 days in a row',                 '🔥', 50,  'streak_days>=3'),
    ('streak_7',       'Week Warrior',     'Logged in 7 days in a row',                 '⚡', 100, 'streak_days>=7'),
    ('mood_explorer',  'Mood Explorer',    'Logged 5 different emotions',               '🌈', 40,  'unique_emotions>=5'),
    ('healthy_eater',  'Healthy Eater',    'Followed 10 food recommendations',          '🥗', 60,  'recommendations_followed>=10'),
    ('level_5',        'Rising Star',      'Reached level 5',                           '⭐', 50,  'level>=5'),
    ('level_10',       'EmoEat Champion',  'Reached level 10',                          '🏆', 200, 'level>=10');

-- ---- User Badges (join table) ----
CREATE TABLE IF NOT EXISTS user_badges (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT      NOT NULL,
    badge_id  INT      NOT NULL,
    earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_badge (user_id, badge_id),
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Default admin user (password: admin123) ----
INSERT INTO users (username, email, password_hash, xp, level, is_admin) VALUES
    ('admin', 'admin@emoeat.local', '$2y$10$hjosMERy.xMlQ1pIXzGLNeuzQPGy4R7H/ievISHNklTHOPCPLCAsS', 0, 1, 1);

-- ---- LLM Configuration (admin-managed) ----
CREATE TABLE IF NOT EXISTS llm_config (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    config_key  VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO llm_config (config_key, config_value) VALUES
    ('provider',      'openai'),
    ('openai_model',  'gpt-3.5-turbo'),
    ('openai_key',    ''),
    ('openai_max_tokens', '1500'),
    ('openai_temperature', '0.7'),
    ('ollama_host',   'http://host.docker.internal:11434'),
    ('ollama_model',  'tinyllama');

-- ---- API Request Logs (telemetry) ----
CREATE TABLE IF NOT EXISTS api_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          DEFAULT NULL,
    endpoint    VARCHAR(200) NOT NULL,
    method      VARCHAR(10)  NOT NULL DEFAULT 'GET',
    status_code INT          NOT NULL DEFAULT 200,
    duration_ms INT          DEFAULT NULL,
    llm_provider VARCHAR(20) DEFAULT NULL,
    tokens_used INT          DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
