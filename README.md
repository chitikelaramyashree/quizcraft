# QuizCraft

A full-featured quiz platform built with PHP, MySQL, HTML, CSS, and JavaScript.

## 🚀 Features

- User authentication (login/register)
- Quiz builder for creators (add questions, choices, correct answers)
- Quiz player for takers (immediate feedback, final score, retry)
- Explore quizzes by category and popularity
- Leaderboard for top quiz masters
- Modern, responsive UI

## 🗄️ Database Setup

1. **Create a database (e.g., `quizcraft`)**
2. **Run these SQL commands:**

```sql
-- users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- quizzes table
CREATE TABLE quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  category VARCHAR(50),
  question_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- questions table
CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  text TEXT NOT NULL,
  difficulty ENUM('Easy','Medium','Hard') DEFAULT 'Medium',
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- answers table
CREATE TABLE answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  text TEXT NOT NULL,
  is_correct BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- user_answers table
CREATE TABLE user_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  quiz_id INT NOT NULL,
  question_id INT NOT NULL,
  answer_id INT NOT NULL,
  is_correct BOOLEAN DEFAULT 0,
  answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id),
  FOREIGN KEY (question_id) REFERENCES questions(id),
  FOREIGN KEY (answer_id) REFERENCES answers(id)
);

-- leaderboard table
CREATE TABLE leaderboard (
  user_id INT NOT NULL,
  quiz_id INT NOT NULL,
  score INT DEFAULT 0,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, quiz_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);
```

3. **Set up your `db.php` with your database credentials.**
- Edit `db.php` and set your database credentials:

  ```
  $host = 'localhost';
  $db   = 'quizcraft';
  $user = 'your_db_user';
  $pass = 'your_db_password';
  ```

4. **Run the app**

- Place the project in your web server’s root directory (e.g., `htdocs` for XAMPP)
- Visit `http://localhost/quizcraft/index.html` in your browser


## 💻 Usage

- Register and log in
- Create quizzes
- Take quizzes and get instant feedback
- View the leaderboard

## 🏗️ Project Structure

- `index.html` - Homepage and explore quizzes
- `browse.html` - All quizzes
- `create_quizz.html` - Quiz builder
- `take-quiz.php` - Quiz player
- `get_leaderboard.php`, `get_quizzes.php` - API endpoints

