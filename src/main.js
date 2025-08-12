import './style.css'

// Initialize the Care Tracker application
document.querySelector('#app').innerHTML = `
  <div class="app">
    <div class="toolbar">
      <div class="title">
        <div class="avatar" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="#87a2ff" stroke-width="1.4"/>
            <path d="M3 21a9 9 0 0 1 18 0" stroke="#87a2ff" stroke-width="1.4"/>
          </svg>
        </div>
        <div>
          <h1>Momilove52 Care Tracker <span class="privacy">Private</span></h1>
          <div class="chipline">Healthcare tracking and caregiver support system</div>
        </div>
      </div>
    </div>
    <div class="status">Application loaded successfully!</div>
  </div>
`

console.log('Momilove52 Care Tracker initialized')