<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lowongan Pekerjaan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
  <div class="logo-title">
    <img src="Lambang_Kabupaten_Gresik.png" alt="Logo Gresik" class="logo-gresik" />
    <h1>Lowongan Pekerjaan</h1>
  </div>
    <nav>
      <button id="btnAll" class="active" aria-pressed="true">All Jobs</button>
      <button id="btnSaved" aria-pressed="false">Saved Jobs</button>
      <button id="btnAddJob">Add New Job</button>
    </nav>
  </header>
  <main>
    <div id="notification" class="notification" role="alert" aria-live="polite"></div>
    <section class="search-panel">
      <input type="text" id="searchInput" class="search-input" placeholder="Search jobs..." aria-label="Search jobs">
      <select id="sortSelect" class="filter-select" aria-label="Sort jobs">
        <option value="relevance">Sort by Relevance</option>
        <option value="salaryAsc">Salary: Low to High</option>
        <option value="salaryDesc">Salary: High to Low</option>
        <option value="locationAsc">Location: A-Z</option>
        <option value="locationDesc">Location: Z-A</option>
      </select>
    </section>
    <section class="jobs-wrapper">
      <aside class="filters-sidebar">
        <div class="filters">
          <div class="filter-group" id="filterType">
            <h3>Job Type</h3>
            <div class="filter-options">
              <label><input type="checkbox" value="Full-time"> Full-time</label>
              <label><input type="checkbox" value="Part-time"> Part-time</label>
              <label><input type="checkbox" value="Contract"> Contract</label>
              <label><input type="checkbox" value="Remote"> Remote</label>
              <label><input type="checkbox" value="Internship"> Internship</label>
            </div>
          </div>
          <div class="filter-group" id="filterCategory">
            <h3>Categories</h3>
            <div class="filter-options">
              <label><input type="checkbox" value="Accounting/Finance"> Accounting/Finance</label>
              <label><input type="checkbox" value="Admin/Clerical"> Admin/Clerical</label>
              <label><input type="checkbox" value="Advertising/PR/Events"> Advertising/PR/Events</label>
              <label><input type="checkbox" value="Architecture/Interior Design"> Architecture/Interior Design</label>
              <label><input type="checkbox" value="Arts/Creative/Design"> Arts/Creative/Design</label>
              <label><input type="checkbox" value="Building/Construction"> Building/Construction</label>
              <label><input type="checkbox" value="Customer Service"> Customer Service</label>
              <label><input type="checkbox" value="Education/Training"> Education/Training</label>
              <label><input type="checkbox" value="Engineering"> Engineering</label>
              <label><input type="checkbox" value="Hospitality/F&B/Travel"> Hospitality/F&B/Travel</label>
              <label><input type="checkbox" value="Human Resources"> Human Resources</label>
              <label><input type="checkbox" value="IT/Computer - Hardware"> IT/Computer - Hardware</label>
              <label><input type="checkbox" value="IT/Computer - Software"> IT/Computer - Software</label>
              <label><input type="checkbox" value="Legal"> Legal</label>
              <label><input type="checkbox" value="Manufacturing/Operations"> Manufacturing/Operations</label>
              <label><input type="checkbox" value="Marketing/Business Development"> Marketing/Business Development</label>
              <label><input type="checkbox" value="Medical/Healthcare"> Medical/Healthcare</label>
              <label><input type="checkbox" value="Other"> Other</label>
              <label><input type="checkbox" value="Purchasing/Logistics"> Purchasing/Logistics</label>
              <label><input type="checkbox" value="Quality Assurance"> Quality Assurance</label>
              <label><input type="checkbox" value="Research/Science"> Research/Science</label>
              <label><input type="checkbox" value="Sales/Business Development"> Sales/Business Development</label>
              <label><input type="checkbox" value="Secretary/PA"> Secretary/PA</label>
              <label><input type="checkbox" value="Telecoms"> Telecoms</label>
              <label><input type="checkbox" value="Trade/Services"> Trade/Services</label>
            </div>
          </div>
          <div class="filter-group" id="filterLocation">
            <h3>Lokasi</h3>
            <div id="locationFilterOptions" class="filter-options"></div>
          </div>
          <div class="filter-group" id="filterSalary">
            <h3>Salary Range (Million IDR)</h3>
            <div class="filter-options">
              <label><input type="checkbox" value="0-5"> 0 - 5</label>
              <label><input type="checkbox" value="5-10"> 5 - 10</label>
              <label><input type="checkbox" value="10-15"> 10 - 15</label>
              <label><input type="checkbox" value="15-25"> 15 - 25</label>
              <label><input type="checkbox" value="25+"> 25+</label>
            </div>
          </div>
        </div>
      </aside>
      <div class="jobs-list" id="jobList" tabindex="0" role="region" aria-label="Job listings"></div>
    </section>
    <nav class="pagination" id="paginationNav" role="navigation" aria-label="Pagination"></nav>
  </main>
  <div class="modal-backdrop" id="modalBackdrop" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="modalJobTitle">
      <button class="btn-close" id="modalCloseBtn" aria-label="Close modal">×</button>
      <h2 id="modalJobTitle"></h2>
      <div class="company" id="modalCompany"></div>
      <div class="job-info">
        <span id="modalLocation"></span>
        <span id="modalType"></span>
        <span id="modalSalary"></span>
      </div>
      <div class="tags" id="modalTags"></div>
      <div class="description" id="modalDescription"></div>
      <a href="#" class="btn-apply-modal" id="modalApplyBtn" target="_blank">Apply Now</a>
    </div>
  </div>
  <div class="modal-backdrop" id="addJobModalBackdrop" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="addJobModalTitle">
      <button class="btn-close" id="addJobModalCloseBtn" aria-label="Close modal">×</button>
      <h2 id="addJobModalTitle">Add New Job</h2>
      <form id="addJobForm">
        <div class="form-group">
          <label for="jobTitle">Job Title</label>
          <input type="text" id="jobTitle" name="title" required class="search-input" placeholder="Enter job title">
        </div>
        <div class="form-group">
          <label for="jobCompany">Company</label>
          <input type="text" id="jobCompany" name="company" required class="search-input" placeholder="Enter company name">
        </div>
        <div class="form-group">
          <label for="jobLocation">Location</label>
          <input type="text" id="jobLocation" name="location" required class="search-input" placeholder="Enter location">
        </div>
        <div class="form-group">
          <label for="jobSalaryMin">Salary Minimum (Million IDR)</label>
          <input type="number" id="jobSalaryMin" name="salary_min" required class="search-input" placeholder="e.g., 12" step="0.01">
        </div>
        <div class="form-group">
          <label for="jobSalaryMax">Salary Maximum (Million IDR)</label>
          <input type="number" id="jobSalaryMax" name="salary_max" required class="search-input" placeholder="e.g., 18" step="0.01">
        </div>
        <div class="form-group">
          <label for="jobType">Job Type</label>
          <select id="jobType" name="type" required class="filter-select">
            <option value="">Select Job Type</option>
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
            <option value="Contract">Contract</option>
            <option value="Remote">Remote</option>
            <option value="Internship">Internship</option>
          </select>
        </div>
        <div class="form-group">
          <label for="jobCategories">Categories (comma-separated)</label>
          <input type="text" id="jobCategories" name="categories" required class="search-input" placeholder="e.g., IT/Computer - Software, Marketing">
        </div>
        <div class="form-group">
          <label for="jobDescription">Description</label>
          <textarea id="jobDescription" name="description" required class="search-input" placeholder="Enter job description and requirements"></textarea>
        </div>
        <div class="form-group">
          <label for="jobUrl">Apply URL</label>
          <input type="url" id="jobUrl" name="url" required class="search-input" placeholder="e.g., https://example.com/apply">
        </div>
        <button type="submit" class="btn-apply-modal">Submit Job</button>
      </form>
    </div>
  </div>
  <footer>
    © 2025 Lowongan Pekerjaan. •
    Contact us: <a href="mailto:contact@lowonganpekerjaan.id">intannurainiii57gmail.com</a> •
    Phone: <a href="tel:+6285855775095">+628718696055</a>
  </footer>
  <script src="js.js"></script>
</body>
</html>