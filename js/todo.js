// DOM Elemanları
const todoInput = document.getElementById("todo-input");
const todoList = document.getElementById("todo-list");

// To-Do Listesi
function loadTodos() {
  try {
    // Önce sunucudan yükle
    fetch('api/load_progress.php')
      .then(response => {
        if (!response.ok) throw new Error(`Sunucu hatası: ${response.status}`);
        return response.json();
      })
      .then(data => {
        userData.todos = data.todos || [];
        renderTodoList();
      })
      .catch(error => {
        console.error("Sunucu hatası:", error);
        // Sunucu çalışmıyorsa localStorage'dan yükle
        loadTodosFromLocalStorage();
      });
  } catch (e) {
    console.error("To-Do yükleme hatası:", e);
    loadTodosFromLocalStorage();
  }
}

// To-Do Listesi (localStorage'dan)
function loadTodosFromLocalStorage() {
  const todos = JSON.parse(localStorage.getItem("fit40_todos")) || [];
  userData.todos = todos.map(text => ({ text, completed: false }));
  renderTodoList();
}

// To-Do Listesini Render Et
function renderTodoList() {
  todoList.innerHTML = '';
  
  userData.todos.forEach((todo, index) => {
    const li = document.createElement("li");
    if (todo.completed) li.classList.add("completed");
    
    li.innerHTML = `
      <span>${todo.text}</span>
      <div class="todo-actions">
        <button class="complete-btn" data-index="${index}">✓</button>
        <button class="delete-btn" data-index="${index}">✖</button>
      </div>
    `;
    todoList.appendChild(li);
  });
  
  // Event listener'lar
  document.querySelectorAll('.complete-btn').forEach(btn => {
    btn.addEventListener('click', toggleComplete);
  });
  
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', deleteTodo);
  });
}

// Yeni görev ekle
function addTodo() {
  const text = todoInput.value.trim();
  if (!text) return;
  
  const newTodo = { text, completed: false };
  userData.todos.push(newTodo);
  
  // Sunucuya kaydet
  saveTodoToServer(text)
    .then(() => {
      todoInput.value = "";
      renderTodoList();
    })
    .catch(error => {
      console.error("To-Do kaydedilemedi:", error);
      // Yedek olarak localStorage'a kaydet
      const todos = JSON.parse(localStorage.getItem("fit40_todos")) || [];
      todos.push(text);
      localStorage.setItem("fit40_todos", JSON.stringify(todos));
      todoInput.value = "";
      renderTodoList();
    });
}

// Tamamlandı işaretle
function toggleComplete(e) {
  const index = e.target.dataset.index;
  if (index < userData.todos.length) {
    userData.todos[index].completed = !userData.todos[index].completed;
    
    // Sunucuya güncelle
    updateTodoOnServer(userData.todos[index])
      .then(() => renderTodoList())
      .catch(error => {
        console.error("To-Do güncellenemedi:", error);
        // Yedek olarak localStorage'a kaydet
        renderTodoList();
      });
  }
}

// Görev sil
function deleteTodo(e) {
  const index = e.target.dataset.index;
  if (index < userData.todos.length) {
    const todo = userData.todos[index];
    userData.todos.splice(index, 1);
    
    // Sunucudan sil
    deleteTodoFromServer(todo)
      .then(() => renderTodoList())
      .catch(error => {
        console.error("To-Do silinemedi:", error);
        // Yedek olarak localStorage'dan sil
        renderTodoList();
      });
  }
}

// Sunucuya kaydet
async function saveTodoToServer(text) {
  const response = await fetch('api/save_todo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text })
  });
  
  if (!response.ok) {
    throw new Error(`Sunucu hatası: ${response.status}`);
  }
  
  const data = await response.json();
  return data;
}

// Sunucuda güncelle
async function updateTodoOnServer(todo) {
  const response = await fetch('api/update_todo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: todo.id,
      completed: todo.completed
    })
  });
  
  if (!response.ok) {
    throw new Error(`Sunucu hatası: ${response.status}`);
  }
  
  return response.json();
}

// Sunucudan sil
async function deleteTodoFromServer(todo) {
  const response = await fetch('api/delete_todo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: todo.id })
  });
  
  if (!response.ok) {
    throw new Error(`Sunucu hatası: ${response.status}`);
  }
  
  return response.json();
}

// Enter tuşuyla ekle
if (todoInput) {
  todoInput.addEventListener("keypress", e => {
    if (e.key === 'Enter') addTodo();
  });
}

// Butona tıkla
if (document.getElementById("add-todo")) {
  document.getElementById("add-todo").addEventListener("click", addTodo);
}

// Sayfa yüklendiğinde
document.addEventListener("DOMContentLoaded", () => {
  if (todoList) {
    loadTodos();
  }
});