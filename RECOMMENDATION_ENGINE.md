# AI-Powered Job Recommendation System — Technical Documentation

This document describes the **Intelligent Rule-Based Job Recommendation Engine** implemented in the Training & Placement Management System (TPMS).

---

## 1. Overview & Architecture

The recommendation engine calculates a personalized **Match Score (0% to 100%)** for each student against active job openings based on 4 criteria:

1. **Required Skills** (50% weight)
2. **Academic Branch Eligibility** (20% weight)
3. **Minimum CGPA Qualification** (20% weight)
4. **Preferred Work Location** (10% weight)

It generates dynamic, **human-readable natural language explanations** detailing why a specific job was recommended.

The engine is built modularly in [`services/JobRecommendationService.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/services/JobRecommendationService.php) with **zero external AI API dependencies**.

---

## 2. Recommendation Algorithm Formula

$$\text{Total Match Score} = \text{SkillsScore} + \text{BranchScore} + \text{CgpaScore} + \text{LocationScore}$$

### A. Skills Match (50% Weight)
- Both student skills (`students.skills`) and job required skills (`jobs.skills_required`) are tokenized (lowercase, sanitized).
- **Match Ratio Formula**:
  $$\text{SkillsScore} = \left( \frac{\text{Matched Skills Count}}{\text{Total Job Required Skills Count}} \right) \times 50\%$$
- If a job specifies no required skills, a default base score of 50% is awarded.

### B. Branch Match (20% Weight)
- Compares student's branch against job's `eligibility_branches`.
- **Score**:
  - `20%` if student branch matches eligible branches (or job is open to 'All' branches).
  - `0%` if student branch is ineligible.

### C. CGPA Match (20% Weight)
- Compares student's CGPA against job's `eligibility_cgpa`.
- **Score**:
  - `20%` if Student CGPA $\ge$ Job Minimum CGPA (or min CGPA is unspecified / 0).
  - `10%` partial credit if Student CGPA is within 0.5 points below required minimum.
  - `0%` if Student CGPA is below requirement by > 0.5 points.

### D. Location Match (10% Weight)
- Compares job location against student's `preferred_location`, `city`, or `state`.
- **Score**:
  - `10%` if job location matches student's preferred location / city, or if job is `Remote` / `Any`.
  - `5%` if state matches.
  - `0%` if location does not match.

---

## 3. Score Classification & UI Badging

| Calculated Match Score | Recommendation Label | Badge Style |
| :--- | :--- | :--- |
| **85% – 100%** | **Recommended** / High Match | Green (`bg-success` / Emerald) |
| **70% – 84%** | **Good Match** | Blue (`bg-primary` / Ocean) |
| **50% – 69%** | **Average Match** | Yellow (`bg-warning` / Amber) |
| **0% – 49%** | Standard Match | Gray (`bg-secondary`) |

---

## 4. Natural Language Explanation Generator

The engine synthesizes clear, human-readable explanations:

### Example Output 1:
> *"Recommended because your Java, PHP and MySQL skills match this job, your Computer Science branch is eligible, and your CGPA (8.50) satisfies company requirements (min 7.00)."*

### Example Output 2:
> *"Recommended because your Python and Data Analysis skills match this job and the job location (Pune) aligns with your preferred work location."*

---

## 5. UI Components & Integration

### 1. Student Dashboard (`/student/dashboard`)
- Includes a dedicated **"AI Recommended Jobs For You"** card section.
- Displays match badges, progress indicators, natural language explanation callouts, and 1-click apply buttons.

### 2. Job Browsing Page (`/student/jobs`)
- Automatically orders active jobs by **Match Percentage Descending** so top recommended jobs appear at the top.
- Job cards feature match score badges (e.g. `95% Match - Recommended`) and **"Why Recommended"** callout boxes.

### 3. Student Profile Editing (`/student/profile/edit`)
- Allows students to update their **Preferred Work Location(s)** (e.g. `Pune, Mumbai, Remote`), **Skills**, **CGPA**, and **Branch** to improve recommendation accuracy.

---

## 6. Modified & Created Files List

1. [`services/JobRecommendationService.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/services/JobRecommendationService.php) (NEW recommendation engine class)
2. [`database/migrations/008_add_preferred_location_to_students.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/database/migrations/008_add_preferred_location_to_students.php) (NEW migration)
3. [`controllers/StudentController.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/controllers/StudentController.php) (MODIFIED)
4. [`views/student/dashboard.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/views/student/dashboard.php) (MODIFIED)
5. [`views/student/jobs.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/views/student/jobs.php) (MODIFIED)
6. [`views/student/edit-profile.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/views/student/edit-profile.php) (MODIFIED)
7. [`RECOMMENDATION_ENGINE.md`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/RECOMMENDATION_ENGINE.md) (NEW documentation)
