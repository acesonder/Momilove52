# Momilove52 - Healthcare Caregiving Application

Momilove52 is a healthcare tracking and caregiver support system built as a modern web application using Vite, vanilla JavaScript, and CSS. The application provides comprehensive tracking for patients and caregivers including vitals, medications, appointments, and daily check-ins.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap, Build, and Test the Repository

**NEVER CANCEL any of these commands - they complete quickly but set appropriate timeouts:**

- Install dependencies:
  - `npm install` -- takes 1-2 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Build the application:
  - `npm run build` -- takes 0.4-0.9 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Run tests:
  - `npm run test` -- takes 0.1 seconds (currently just a placeholder). NEVER CANCEL. Set timeout to 30+ seconds.
- Lint the code:
  - `npm run lint` -- takes 0.4 seconds. NEVER CANCEL. Set timeout to 30+ seconds.

### Run the Application

**ALWAYS run the bootstrapping steps first before starting any servers.**

- Development server:
  - `npm run dev` -- starts in 250ms, serves on http://localhost:5173/
  - NEVER CANCEL this command. It runs continuously for development.
- Production preview:
  - `npm run build && npm run preview` -- serves production build on http://localhost:4173/
  - NEVER CANCEL this command. It runs continuously.

### Install Additional Dependencies

**Common installations and their timings:**

- ESLint (already installed): `npm install -D eslint` -- takes 25 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Playwright for testing: `npm install -D playwright` -- takes 1-2 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Express.js (already installed): `npm install express` -- takes 1 second. NEVER CANCEL. Set timeout to 60+ seconds.

## Validation

### Manual Validation Requirements

**ALWAYS manually validate any new code using these scenarios:**

1. **Application Startup Validation**:
   - Run `npm run dev`
   - Navigate to http://localhost:5173/
   - Verify the page loads with "Momilove52 Care Tracker" title
   - Check browser console shows "Momilove52 Care Tracker initialized"
   - Verify the application displays the header with avatar and "Application loaded successfully!" message

2. **Build Validation**:
   - Run `npm run build`
   - Verify dist/ directory is created with index.html and assets
   - Run `npm run preview`
   - Navigate to http://localhost:4173/
   - Verify production build loads correctly

3. **Lint Validation**:
   - Run `npm run lint`
   - Verify no errors are reported
   - Any new JavaScript files should pass ESLint checks

### Automated Testing Setup

- Playwright is available for browser automation testing
- Use `playwright-browser_navigate` to test actual application functionality
- ALWAYS take screenshots of UI changes to document functionality

## Common Tasks

### Repository Structure
```
.
├── README.md                    # Comprehensive feature specification
├── package.json                 # Dependencies and scripts
├── index.html                   # Application entry point
├── src/
│   ├── main.js                  # Application initialization
│   └── style.css               # Application styles
├── dist/                        # Build output (gitignored)
├── node_modules/               # Dependencies (gitignored)
├── eslint.config.js            # ESLint configuration
└── .github/
    └── copilot-instructions.md  # This file
```

### Key Files to Monitor

- **Always check `src/main.js`** after making changes to application logic
- **Always check `src/style.css`** after making changes to styling
- **Always check `package.json`** when adding new scripts or dependencies
- **Always run `npm run lint`** before committing changes

### Package.json Scripts
```json
{
  "dev": "vite",           // Development server
  "build": "vite build",   // Production build  
  "preview": "vite preview", // Preview production build
  "lint": "eslint src/",   // Lint source code
  "test": "echo \"No tests specified yet\" && exit 0"
}
```

## Technology Stack

- **Build Tool**: Vite 7.1.1 (fast development server and build)
- **Runtime**: Node.js v20.19.4, npm 10.8.2
- **Linting**: ESLint with flat config format
- **Testing**: Playwright for browser automation (optional)
- **Styling**: Vanilla CSS with CSS custom properties
- **JavaScript**: ES modules, vanilla JavaScript

## Application Features Overview

The README.md contains the complete feature specification including:

1. **Core Patient & Caregiver Features** - profiles, emergency contacts, consent management
2. **Medical & Health Tracking** - vitals, medication, pain tracking, wound care
3. **Appointment & Scheduling Tools** - calendar, telehealth integration
4. **Communication & Collaboration** - secure messaging, voice notes
5. **Document & File Management** - secure uploads, PDF exports
6. **UI/UX & Accessibility** - dark purple theme, dynamic font sizing
7. **Local Resource Integration** - Milford, Saskatchewan specific resources
8. **Automation & AI Assistance** - smart symptom checker, predictive alerts
9. **Analytics & Reporting** - trend analysis, adherence tracking
10. **Engagement & Education** - wellness tips, interactive modules

## Working with the HTML Mockup

The README.md contains a complete HTML mockup showing:
- Two-panel layout (patient and caregiver)
- Tab navigation system
- Daily check-in forms with mood selection
- Statistics dashboard
- Responsive design with purple theme

**To extract and test the mockup**:
1. Copy HTML from lines 207-649 of README.md
2. Save as a .html file
3. Open in browser or serve via HTTP server

## Troubleshooting

### Build Issues
- If `npm run build` fails, check for syntax errors with `npm run lint`
- If dependencies are missing, run `npm install`
- Clear build cache by deleting `dist/` directory

### Development Server Issues
- If port 5173 is in use, Vite will automatically use the next available port
- Check that no other development servers are running
- Verify no proxy or firewall is blocking localhost connections

### Linting Issues
- ESLint configuration is in `eslint.config.js` using flat config format
- Common issues: unused variables (warnings), missing imports
- Fix automatically with `npx eslint src/ --fix` where possible

## Critical Reminders

- **NEVER CANCEL builds or long-running commands** - all commands complete in under 30 seconds
- **ALWAYS validate functionality manually** after making changes
- **ALWAYS run linting** before committing code changes
- **Set timeouts of 60+ seconds** for build commands even though they're fast
- **Use Playwright for UI testing** when making interface changes
- **Reference the comprehensive README.md** for complete feature specifications