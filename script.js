const authContainer = document.getElementById('auth-buttons');

// Check login status from backend
fetch('check_auth.php', { credentials: 'include' })
  .then(res => res.json())
  .then(data => {
    if (data.loggedIn) {
      authContainer.innerHTML = `
        <a href="create_quizz.html" class="btn btn-outline">
          <i data-lucide="plus-circle" class="icon" style="vertical-align: middle;"></i>
          <span class="hidden-on-mobile">Create Quiz</span>
        </a>
        <a href="profile.html" class="btn btn-outline" >
          <i data-lucide="user" class="icon" style="vertical-align: middle;"></i>
          <span class="hidden-on-mobile">${data.userName}</span>
        </a>
        <a  href="logout.php" class="btn btn-primary" style="padding: 0.8rem">
           Logout
        </a>
      `;
    } else {
      authContainer.innerHTML = `
        <a href="./login.html" class="btn btn-outline">Log In</a>
        <a href="./signup.html" class="btn btn-primary">Sign Up</a>
      `;
    }
    lucide.createIcons();
  })
  .catch(err => {
    // Fallback if backend fails
    authContainer.innerHTML = `
      <a href="/login" class="btn btn-outline">Log In</a>
      <a href="/signup" class="btn btn-primary">Sign Up</a>
    `;
    lucide.createIcons();
  });

// Replace hardcoded popularQuizzes with real data
fetch('get_quizzes.php')
  .then(res => res.json())
  .then(quizzes => {
    const quizGrid = document.getElementById('quiz-cards');
    quizGrid.innerHTML = '';
    quizzes.forEach(quiz => {
      const card = document.createElement('div');
      card.className = 'quiz-card';
      card.innerHTML = `
        <h3>${quiz.title}</h3>
        <p>${quiz.description}</p>
        <div class="quiz-stats">
          <span>${quiz.question_count} questions</span>
          <span>⭐ ${quiz.rating}</span>
        </div>
        <button class="play-btn">Play Now</button>
      `;
      quizGrid.appendChild(card);
    });
  });

// Leaderboard Population
function loadLeaderboard() {
  fetch('get_leaderboard.php')
    .then(res => res.json())
    .then(leaders => {
      const list = document.getElementById('leaderboard-list');
      list.innerHTML = '';
      leaders.forEach((player, idx) => {
        list.innerHTML += `
          <div class="leaderboard-item">
            <span class="rank">${getMedal(idx+1)}</span>
            <span class="user">${player.username}</span>
            <span class="score">${player.score} pts</span>
          </div>`;
      });
    });
}


lucide.createIcons();

function getMedal(rank) {
  if(rank === 1) return '🏆';
  if(rank === 2) return '🥈';
  if(rank === 3) return '🥉';
  return rank;
}

function getInitial(username) {
  return username.charAt(0).toUpperCase();
}

function renderLeaderboard(leaders) {
  const list = document.getElementById('leaderboard-list');
  list.innerHTML = '';
  leaders.forEach((player, idx) => {
    list.innerHTML += `
      <li>
        <span style="display:flex;align-items:center;gap:12px;">
          <span style="font-size:1.5em">${getMedal(idx+1)}</span>
          <span class="leaderboard-avatar">${getInitial(player.username)}</span>
          <span>${player.username}</span>
        </span>
        <span>${player.score} pts</span>
      </li>
    `;
  });
}

function fetchLeaderboard() {
  fetch('get_leaderboard.php')
    .then(res => res.json())
    .then(renderLeaderboard);
}

fetchLeaderboard();
setInterval(fetchLeaderboard, 2000);

// // Replace the static quiz cards with dynamic content
// function loadQuizzes() {
//   fetch('get_quizzes.php')
//       .then(res => res.json())
//       .then(quizzes => {
//           const quizGrid = document.getElementById('quiz-cards');
//           quizGrid.innerHTML = quizzes.map(quiz => `
//               <div class="quiz-card">
//                   <div class="quiz-header">
//                       <span class="quiz-category">${quiz.category}</span>
//                       <span class="quiz-questions">${quiz.question_count} questions</span>
//                   </div>
//                   <h3>${quiz.title}</h3>
//                   <p>${quiz.description}</p>
//                   <div class="quiz-footer">
//                       <a href="play_quiz.php?id=${quiz.id}" class="btn-play">Play Now</a>
//                       <span class="quiz-date">Created: ${quiz.created_at}</span>
//                   </div>
//               </div>
//           `).join('');
//       });
// }

// // Call this on page load
// loadQuizzes();
// // In your create_quizz.html's form submission handler:
// fetch('create_quizz.php', {
//   method: 'POST',
//   headers: {
//       'Content-Type': 'application/json'
//   },
//   body: JSON.stringify({
//       tit
// le: document.getElementById('quizTitle').value,
//       description: document.getElementById('quizDescription').value,
//       category: document.getElementById('quizCategory').value,
//       questions: questions
//   })
// })
// .then(response => response.json())
// .then(data => {
//   if (data.success) {
//       window.location.href = `quiz.html?id=${data.quiz_id}`;
//   } else {
//       alert('Error saving quiz: ' + (data.error || 'Unknown error'));
//   }
// })
// .catch(error => {
//   console.error('Error:', error);
//   alert('Failed to save quiz');
// });

// // Fetch and display quizzes in the Explore section
// fetch('get_quizzes.php')
//   .then(res => res.json())
//   .then(quizzes => {
//     const quizGrid = document.getElementById('quiz-cards');
//     quizGrid.innerHTML = '';
//     quizzes.forEach(quiz => {
//       const card = document.createElement('div');
//       card.className = 'quiz-card';
//       card.innerHTML = `
//         <div>
//           <h3>${quiz.title}</h3>
//           <p>${quiz.description || 'No description.'}</p>
//           <span>Category: ${quiz.category || 'N/A'}</span><br>
//           <span>Questions: ${quiz.question_count}</span><br>
//           <span>Created: ${quiz.created_at}</span>
//         </div>
//       `;
//       quizGrid.appendChild(card);
//     });
//   });


  
