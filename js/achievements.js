// DOM Elemanı
const achievementsList = document.getElementById("achievements-list");

// Tüm rozetler
const allBadges = [
  {
    id: "first_step",
    title: "İlk Adım",
    description: "İlk egzersizini tamamladın!",
    icon: "achievements/icons/first-step.svg",
    condition: "first_workout"
  },
  {
    id: "streak_7",
    title: "7 Gün Sürekli",
    description: "7 gün üst üste egzersiz yaptın!",
    icon: "achievements/icons/fire.svg",
    condition: "streak:7"
  },
  {
    id: "plank_master",
    title: "Plank Kahramanı",
    description: "Toplam 5 dakika plank tuttun!",
    icon: "achievements/icons/plank.svg",
    condition: "plank_seconds:300"
  },
  {
    id: "balance_expert",
    title: "Denge Uzmanı",
    description: "5 kez denge egzersizi yaptın.",
    icon: "achievements/icons/balance.svg",
    condition: "exercise_count:Tek Ayak Denge:5"
  },
  {
    id: "flexible_body",
    title: "Esnek Vücut",
    description: "10 kez esneme egzersizi yaptın.",
    icon: "achievements/icons/stretch.svg",
    condition: "exercise_count:Esnetme:10"
  }
];

// Kazanılan rozetler
let earnedBadges = JSON.parse(localStorage.getItem("fit40_earned_badges")) || [];

// Rozetleri yükle
function loadAchievements() {
  achievementsList.innerHTML = '';
  
  allBadges.forEach(badge => {
    const isEarned = earnedBadges.some(b => b.id === badge.id);
    
    const div = document.createElement('div');
    div.className = 'badge-item';
    div.innerHTML = `
      <img src="${badge.icon}" alt="${badge.title}">
      <span class="title">${badge.title}</span>
    `;
    
    if (isEarned) {
      div.title = badge.description;
      div.classList.add('earned');
    } else {
      div.style.opacity = '0.5';
      div.title = 'Kazanılmadı: ' + badge.description;
    }
    
    div.addEventListener('click', () => showBadgeDetail(badge, isEarned));
    achievementsList.appendChild(div);
  });
}

// Rozet detay popup
function showBadgeDetail(badge, isEarned) {
  const popup = document.createElement('div');
  popup.className = 'badge-popup';
  popup.innerHTML = `
    <div class="popup-content">
      <span class="close">&times;</span>
      <img src="${badge.icon}" alt="${badge.title}">
      <h3>${badge.title}</h3>
      <p>${badge.description}</p>
      <div class="popup-footer">
        <span class="status">${isEarned ? 'Kazanıldı' : 'Kazanılmadı'}</span>
        ${isEarned ? '<button class="share-btn">📱 Paylaş</button>' : ''}
      </div>
    </div>
  `;
  
  document.body.appendChild(popup);
  
  // Kapatma butonu
  popup.querySelector('.close').addEventListener('click', () => {
    document.body.removeChild(popup);
  });
  
  // Paylaş butonu
  const shareBtn = popup.querySelector('.share-btn');
  if (shareBtn) {
    shareBtn.addEventListener('click', () => {
      shareBadge(badge);
      document.body.removeChild(popup);
    });
  }
}

// Rozet paylaşımı
function shareBadge(badge) {
  const text = `Fit40+ ile "${badge.title}" rozetini kazandım! 💪 #Fit40plus`;
  
  if (navigator.share) {
    navigator.share({
      title: 'Fit40+ Rozet',
      text: text,
      url: window.location.origin
    }).catch(console.error);
  } else {
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(window.location.origin)}`;
    window.open(url, '_blank');
  }
}

// Yeni rozet kontrolü
function checkAchievements() {
  const today = new Date().toISOString().split('T')[0];
  const completedDays = JSON.parse(localStorage.getItem("fit40_completed_days")) || [];
  
  // İlk adım rozeti
  if (completedDays.length >= 1 && !earnedBadges.some(b => b.id === 'first_step')) {
    addAchievement('first_step');
  }
  
  // 7 gün süreklilik
  if (completedDays.length >= 7 && !earnedBadges.some(b => b.id === 'streak_7')) {
    // Son 7 gün tamamlandı mı?
    const last7Days = Array.from({length: 7}, (_, i) => 
      new Date(Date.now() - i * 86400000).toISOString().split('T')[0]
    ).reverse();
    
    const isStreak = last7Days.every(day => completedDays.includes(day));
    
    if (isStreak) {
      addAchievement('streak_7');
    }
  }
}

// Rozet ekle
function addAchievement(badgeId) {
  const badge = allBadges.find(b => b.id === badgeId);
  if (!badge || earnedBadges.some(b => b.id === badgeId)) return;
  
  earnedBadges.push({
    id: badge.id,
    title: badge.title,
    earned_at: new Date().toISOString()
  });
  
  localStorage.setItem("fit40_earned_badges", JSON.stringify(earnedBadges));
  loadAchievements();
  
  // Popup göster
  const popup = document.createElement('div');
  popup.className = 'achievement-popup';
  popup.innerHTML = `
    <div class="popup">
      <img src="${badge.icon}" alt="${badge.title}">
      <h3>Yeni Rozet Kazandın!</h3>
      <p><strong>${badge.title}</strong></p>
      <p>${badge.description}</p>
    </div>
  `;
  
  document.body.appendChild(popup);
  setTimeout(() => document.body.removeChild(popup), 5000);
  
  // Ses çal
  try {
    new Audio('sounds/achievement.mp3').play();
  } catch (e) { }
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
  loadAchievements();
  checkAchievements();
});