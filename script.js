// DOM Elemanları
const levelRadios = document.querySelectorAll('input[name="level"]');
const loadPlanBtn = document.getElementById("load-plan");
const startWorkoutBtn = document.getElementById("start-workout");
const timerDisplay = document.getElementById("timer");
const exerciseInfo = document.getElementById("exercise-info");
const exerciseGif = document.getElementById("exercise-gif");
const workoutArea = document.getElementById("workout-area");
const pauseBtn = document.getElementById("pause-resume");
const prevBtn = document.getElementById("prev-exercise");
const nextBtn = document.getElementById("next-exercise");
const workoutTitle = document.getElementById("workout-title");

// Mola elemanları
const breakAdviceSection = document.querySelector(".break-advice");
const breakAdviceContent = document.getElementById("break-advice-content");
const breakTimerDisplay = document.getElementById("break-timer");
const extendBreakBtn = document.getElementById("extend-break");
const skipBreakBtn = document.getElementById("skip-break");

// Sesler
const sounds = {
  start: null,
  rest: null,
  complete: null,
  achievement: null
};

// Değişkenler
let currentPlan = null;
let currentDayExercises = [];
let currentExerciseIndex = 0;
let timeLeft = 0;
let timer = null;
let isPaused = false;
let userData = {
  completedDays: [],
  level: "beginner"
};

// Bugün hangi gün?
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const today = days[new Date().getDay() === 0 ? 6 : new Date().getDay() - 1];

// Sesleri yükle
function loadSounds() {
  try {
    sounds.start = new Audio('sounds/start.mp3');
    sounds.rest = new Audio('sounds/rest.mp3');
    sounds.complete = new Audio('sounds/complete.mp3');
    sounds.achievement = new Audio('sounds/achievement.mp3');
    
    // Hata kontrolü
    for (const key in sounds) {
      if (sounds[key]) {
        sounds[key].onerror = () => console.warn(`${key}.mp3 yüklenemedi`);
        sounds[key].load();
      }
    }
  } catch (e) {
    console.warn("Ses dosyaları yüklenemedi:", e);
  }
}

// Mola önerileri
const breakAdviceMessages = [
  "💧 1 su bardağı su için. Vücudunuzun hidrasyonu, egzersiz performansınızı artırır.",
  "🌬️ Derin nefesler alın. 4 saniye nefes alın, 7 saniye tutun, 8 saniye verin.",
  "🧘‍♀️ Kısa bir meditasyon yapın. Zihninizi rahatlatın ve bir sonraki egzersize odaklanın.",
  "👣 Ayaklarınızı hafifçe sallayın. Kan dolaşımını hızlandırın.",
  "🪑 Sırtınızı düz tutarak 1 dakika dinlenin. Vücudunuzun gerilimini azaltın.",
  "👀 Gözlerinizi kapatıp 30 saniye dinlenin. Göz kaslarınızı rahatlatın.",
  "🫁 Nefes egzersizleri yapın. 4-7-8 tekniği ile stres seviyenizi düşürün.",
  "🪞 Aynaya bakın ve pozitif bir cümle söyleyin. 'Bugün iyi bir gün!'"
];

// Mola yönetimi
let breakTimeLeft = 300; // 5 dakika
let breakTimer = null;
let breakExtended = false;

// Rastgele mola önerisi göster
function showBreakAdvice() {
  const randomIndex = Math.floor(Math.random() * breakAdviceMessages.length);
  breakAdviceContent.innerHTML = breakAdviceMessages[randomIndex];
}

// Mola zamanlayıcı
function startBreakTimer() {
  breakTimeLeft = 300; // 5 dakika
  breakExtended = false;
  breakAdviceSection.style.display = "block";
  showBreakAdvice();
  
  clearInterval(breakTimer);
  updateBreakTimerDisplay();
  
  breakTimer = setInterval(() => {
    breakTimeLeft--;
    updateBreakTimerDisplay();
    
    if (breakTimeLeft <= 0) {
      clearInterval(breakTimer);
      completeBreak();
    }
  }, 1000);
}

function updateBreakTimerDisplay() {
  const minutes = Math.floor(breakTimeLeft / 60);
  const seconds = breakTimeLeft % 60;
  breakTimerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function extendBreak() {
  if (!breakExtended) {
    breakTimeLeft += 300; // 5 dakika ekle
    breakExtended = true;
    updateBreakTimerDisplay();
    
    // Yeni bir öneri göster
    showBreakAdvice();
  }
}

function completeBreak() {
  breakAdviceSection.style.display = "none";
  clearInterval(breakTimer);
  currentExerciseIndex++;
  runNextExercise();
}

// Kullanıcı verisini yükle
async function loadUserData() {
  try {
    const res = await fetch('api/load_progress.php');
    if (!res.ok) {
      if (res.status === 401) {
        // Kullanıcı girişi yok, giriş sayfasına yönlendir
        window.location.href = 'auth/login.php';
        return;
      }
      throw new Error(`Sunucu hatası: ${res.status}`);
    }
    
    const data = await res.json();
    userData = {
      completedDays: data.completedDays || [],
      level: data.level || 'beginner'
    };
    
    // Seviyeyi ayarla
    const savedLevel = userData.level || 'beginner';
    const radio = document.querySelector(`input[value="${savedLevel}"]`);
    if (radio) radio.checked = true;
    
    console.log("Kullanıcı verisi yüklendi:", userData);
  } catch (err) {
    console.error("Veri yüklenemedi:", err);
    
    // Yedek olarak localStorage'dan yükle
    const savedLevel = localStorage.getItem('fit40_level') || 'beginner';
    const radio = document.querySelector(`input[value="${savedLevel}"]`);
    if (radio) radio.checked = true;
  }
}

// Plan Yükle
async function loadPlan() {
  const level = document.querySelector('input[name="level"]:checked')?.value || 'beginner';
  const planPath = `plans/${level}.json`;

  try {
    console.log("Yükleniyor:", planPath);
    const response = await fetch(planPath);
    
    if (!response.ok) {
      throw new Error(`Plan dosyası bulunamadı: ${response.status}`);
    }

    currentPlan = await response.json();
    alert(`${currentPlan.title || 'Egzersiz Planı'} yüklendi!`);

    // Bugünün planını yükle
    if (currentPlan.days && currentPlan.days[today]) {
      currentDayExercises = currentPlan.days[today].exercises;
      workoutTitle.textContent = currentPlan.days[today].title;
      exerciseInfo.textContent = "Hazırlanıyor...";
      exerciseGif.style.display = "none";
    } else {
      // Bugünkü plan yoksa, ilk günü yükle
      const firstDay = Object.keys(currentPlan.days)[0];
      currentDayExercises = currentPlan.days[firstDay].exercises;
      workoutTitle.textContent = currentPlan.days[firstDay].title;
      exerciseInfo.textContent = "Hazırlanıyor...";
      exerciseGif.style.display = "none";
    }

    // Arayüzü güncelle
    document.querySelector(".level-selector").style.display = "none";
    document.querySelector(".day-selector").style.display = "block";
  } catch (err) {
    console.error("Plan yüklenemedi:", err);
    alert(`Hata: ${err.message}\n\nLütfen plan dosyalarının doğru yüklendiğini kontrol edin.`);
  }
}

// Egzersize Başla
function startWorkout() {
  if (!currentDayExercises || currentDayExercises.length === 0) {
    alert("Bu gün için plan tanımlanmamış.");
    return;
  }
  
  workoutArea.style.display = "block";
  currentExerciseIndex = 0;
  runNextExercise();
}

// Egzersiz detaylarını göster
function showExerciseDetails(exercise) {
  let tipsHTML = '';
  if (exercise.tips && exercise.tips.length > 0) {
    tipsHTML = '<div class="exercise-tips">';
    tipsHTML += '<h4>İpuçları:</h4>';
    tipsHTML += '<ul>';
    
    exercise.tips.forEach(tip => {
      tipsHTML += `<li>${tip}</li>`;
    });
    
    tipsHTML += '</ul></div>';
  }
  
  return tipsHTML;
}

// Sonraki egzersize geç
function runNextExercise() {
  if (currentExerciseIndex >= currentDayExercises.length) {
    exerciseInfo.innerHTML = "<strong>🎉 Tebrikler!</strong> Bugünki egzersizler tamamlandı.";
    exerciseGif.style.display = "none";
    clearInterval(timer);
    
    // İlerlemeyi kaydet
    const today = new Date().toISOString().split('T')[0];
    if (!userData.completedDays.includes(today)) {
      userData.completedDays.push(today);
      saveProgress(today, userData.level);
      checkAchievements();
    }
    return;
  }

  const ex = currentDayExercises[currentExerciseIndex];
  timeLeft = ex.duration || 0;
  const setName = ex.sets ? ` <em>(${ex.sets} set)</em>` : '';
  
  // Egzersiz başlığını ve bilgilerini güncelle
  workoutTitle.textContent = ex.name;
  
  // Detaylı açıklama
  let detailsHTML = `<strong>${ex.name}</strong>${setName}<br><small>${ex.instruction || ''}</small>`;
  detailsHTML += showExerciseDetails(ex);
  
  exerciseInfo.innerHTML = detailsHTML;
  
  // GIF kontrolü
  if (ex.gif) {
    exerciseGif.src = `exercises/${ex.gif}`;
    exerciseGif.style.display = "block";
  } else {
    exerciseGif.style.display = "none";
  }

  // Ses çal
  try {
    if (ex.name === "Dinlenme") {
      if (sounds.rest) sounds.rest.play().catch(e => console.log("Rest sesi hatası:", e));
      startBreakTimer(); // Mola zamanlayıcısını başlat
      return;
    } else {
      if (sounds.start) sounds.start.play().catch(e => console.log("Start sesi hatası:", e));
    }
  } catch (e) { 
    console.log("Ses çalma hatası:", e); 
  }

  // Süresiz tekrar kontrolü
  if (ex.reps && !ex.duration) {
    // Süresiz tekrar modu
    timerDisplay.textContent = `${ex.reps} tekrar`;
    
    // Kullanıcıya hazır olma süresi ver
    setTimeout(() => {
      if (isPaused) return;
      
      // Geri sayım başlasın
      let countdown = 3;
      const countdownEl = document.createElement('div');
      countdownEl.className = 'countdown';
      countdownEl.textContent = `${countdown}...`;
      timerDisplay.parentElement.appendChild(countdownEl);
      
      const countdownInterval = setInterval(() => {
        if (isPaused) {
          clearInterval(countdownInterval);
          countdownEl.remove();
          return;
        }
        
        if (countdown > 0) {
          countdownEl.textContent = `${countdown}...`;
          countdown--;
        } else {
          clearInterval(countdownInterval);
          countdownEl.remove();
          
          // Tamamlama ilerlemesi ekle
          let progressHTML = `
            <div class="completion-bar">
              <div class="completion-progress" id="completion-progress"></div>
            </div>
          `;
          
          if (!document.getElementById("completion-progress")) {
            exerciseInfo.insertAdjacentHTML('beforeend', progressHTML);
          }
          
          // Tamamlama kontrolü için süre başlat
          const completionTime = ex.reps * 2; // Her tekrar için 2 saniye
          let timeRemaining = completionTime;
          const startTime = Date.now();
          
          const completionInterval = setInterval(() => {
            if (isPaused) return;
            
            timeRemaining--;
            
            // İlerleme yüzdesini hesapla
            const elapsed = Date.now() - startTime;
            const percent = Math.min((elapsed / (completionTime * 1000)) * 100, 100);
            const progress = document.getElementById("completion-progress");
            if (progress) {
              progress.style.width = `${percent}%`;
            }
            
            if (timeRemaining <= 0) {
              clearInterval(completionInterval);
              currentExerciseIndex++;
              runNextExercise();
            }
          }, 1000);
        }
      }, 1000);
    }, 2000);
  } 
  // Süreli egzersizler
  else if (timeLeft > 0) {
    timerDisplay.textContent = formatTime(timeLeft);
    timer = setInterval(updateTimer, 1000);
  } 
  // Süresiz ve tekrarsız (hazırlık)
  else {
    timerDisplay.textContent = "Hazırlan...";
    setTimeout(() => {
      if (!isPaused) {
        currentExerciseIndex++;
        runNextExercise();
      }
    }, 2000);
  }
  
  // Buton durumu
  prevBtn.disabled = currentExerciseIndex === 0;
  nextBtn.disabled = currentExerciseIndex >= currentDayExercises.length - 1;
  pauseBtn.textContent = "⏸ Duraklat";
  isPaused = false;
}

// Timer Güncelleme
function updateTimer() {
  timerDisplay.textContent = formatTime(timeLeft);
  if (timeLeft <= 0) {
    currentExerciseIndex++;
    runNextExercise();
  } else {
    timeLeft--;
  }
}

// Zaman Formatı
function formatTime(seconds) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// İlerlemeyi sunucuya kaydet
async function saveProgress(date, level) {
  if (!date) return;
  
  try {
    const response = await fetch('api/save_progress.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ date, level })
    });
    
    if (!response.ok) {
      if (response.status === 401) {
        // Kullanıcı girişi yok, giriş sayfasına yönlendir
        window.location.href = 'auth/login.php';
        return;
      }
      throw new Error(`Sunucu hatası: ${response.status}`);
    }
    
    console.log("İlerleme kaydedildi");
  } catch (err) {
    console.error("İlerleme kaydedilemedi", err);
    
    // Yedek olarak localStorage'a kaydet
    let completedDays = JSON.parse(localStorage.getItem('fit40_completed_days')) || [];
    if (!completedDays.includes(date)) {
      completedDays.push(date);
      localStorage.setItem('fit40_completed_days', JSON.stringify(completedDays));
    }
    localStorage.setItem('fit40_level', level);
  }
}

// Kontroller
function togglePause() {
  if (isPaused) {
    timer = setInterval(updateTimer, 1000);
    pauseBtn.textContent = "⏸ Duraklat";
    isPaused = false;
  } else {
    clearInterval(timer);
    pauseBtn.textContent = "▶ Devam";
    isPaused = true;
  }
}

function prevExercise() {
  if (currentExerciseIndex > 0) {
    currentExerciseIndex--;
    runNextExercise();
  }
}

function nextExercise() {
  if (currentExerciseIndex < currentDayExercises.length - 1) {
    currentExerciseIndex++;
    runNextExercise();
  }
}

// Event Listener'lar
document.addEventListener("DOMContentLoaded", () => {
  // Sesleri yükle
  loadSounds();
  
  // Kullanıcı verisini yükle
  loadUserData();
  
  // Butonlara event listener ekle
  if (loadPlanBtn) loadPlanBtn.addEventListener("click", loadPlan);
  if (startWorkoutBtn) startWorkoutBtn.addEventListener("click", startWorkout);
  if (pauseBtn) pauseBtn.addEventListener("click", togglePause);
  if (prevBtn) prevBtn.addEventListener("click", prevExercise);
  if (nextBtn) nextBtn.addEventListener("click", nextExercise);
  if (extendBreakBtn) extendBreakBtn.addEventListener("click", extendBreak);
  if (skipBreakBtn) skipBreakBtn.addEventListener("click", completeBreak);
  
  console.log("Uygulama başlatıldı");
});

// Hata izleme
window.onerror = function(message, source, lineno, colno, error) {
  console.error("Genel hata:", {
    message: message,
    source: source,
    line: lineno,
    column: colno,
    error: error
  });
  return true;
};