<?php
/**
 * EmoEat Configuration
 */
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'mysql',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'emoeat',
        'user' => getenv('DB_USER') ?: 'emoeat_user',
        'pass' => getenv('DB_PASS') ?: 'EmoEat2026!',
    ],
    'llm' => [
        'provider'      => getenv('LLM_PROVIDER') ?: 'openai',
        'ollama_host'   => getenv('OLLAMA_HOST') ?: 'http://host.docker.internal:11434',
        'ollama_model'  => getenv('OLLAMA_MODEL') ?: 'tinyllama',
        'openai_key'    => getenv('OPENAI_API_KEY') ?: '',
        'openai_model'  => getenv('OPENAI_MODEL') ?: 'gpt-3.5-turbo',
    ],
    'app' => [
        'name' => 'EmoEat',
        'url'  => 'http://localhost:8080',
    ],
    'gamification' => [
        'xp_per_checkin'  => 10,
        'xp_per_quiz'     => 25,
        'xp_per_level'    => 100,
    ],
];
