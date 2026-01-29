CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(20) NOT NULL,
  email VARCHAR(255) NULL,
  password_hash VARCHAR(255) NULL,
  google_id VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_username (username),
  UNIQUE KEY uniq_email (email),
  UNIQUE KEY uniq_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scores (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  mode VARCHAR(16) NOT NULL DEFAULT 'html',
  score INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_mode_score (mode, score),
  CONSTRAINT fk_scores_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

endpoints:

- `auth/register.php`
- `auth/login.php`
- `auth/logout.php`
- `auth/me.php`
- `api/leaderboard.php`
- `api/submit_score.php`
- `oauth/google_start.php`
- `oauth/google_callback.php`

- Go to Google Cloud Console
-  Create/select a project
-  Enable Google Identity / OAuth
-  Create OAuth Client ID (type: Web application)