let jobs = [];
let filteredJobs = [];
const jobsPerPage = 4;
let currentPage = 1;
let savedJobs = new Set(JSON.parse(localStorage.getItem('savedJobs') || '[]'));

const jobList = document.getElementById('jobList');
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');
const paginationNav = document.getElementById('paginationNav');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalCloseBtn = document.getElementById('modalCloseBtn');
const modalJobTitle = document.getElementById('modalJobTitle');
const modalCompany = document.getElementById('modalCompany');
const modalLocation = document.getElementById('modalLocation');
const modalType = document.getElementById('modalType');
const modalSalary = document.getElementById('modalSalary');
const modalTags = document.getElementById('modalTags');
const modalDescription = document.getElementById('modalDescription');
const modalApplyBtn = document.getElementById('modalApplyBtn');
const filterTypeInputs = document.querySelectorAll('#filterType input[type=checkbox]');
const filterCategoryInputs = document.querySelectorAll('#filterCategory input[type=checkbox]');
const filterSalaryInputs = document.querySelectorAll('#filterSalary input[type=checkbox]');
const locationFilterOptions = document.getElementById('locationFilterOptions');
const btnAll = document.getElementById('btnAll');
const btnSaved = document.getElementById('btnSaved');
const btnAddJob = document.getElementById('btnAddJob');
const addJobModalBackdrop = document.getElementById('addJobModalBackdrop');
const addJobModalCloseBtn = document.getElementById('addJobModalCloseBtn');
const addJobForm = document.getElementById('addJobForm');
const notification = document.getElementById('notification');

function debounce(fn, delay) {
  let timer = null;
  return function (...args) {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

function showNotification(message, type = 'success') {
  notification.textContent = message;
  notification.className = `notification ${type}`;
  notification.style.display = 'block';
  setTimeout(() => {
    notification.style.display = 'none';
  }, 3000);
}

function populateLocationFilters() {
  const locations = Array.from(new Set(jobs.map(job => job.location))).sort((a, b) => a.localeCompare(b));
  locationFilterOptions.innerHTML = '';
  locations.forEach(loc => {
    const label = document.createElement('label');
    label.innerHTML = `<input type="checkbox" value="${loc}" /> ${loc}`;
    locationFilterOptions.appendChild(label);
  });
  addLocationListeners();
}

function addLocationListeners() {
  const locationInputs = document.querySelectorAll('#filterLocation input[type=checkbox]');
  locationInputs.forEach(input => {
    input.removeEventListener('change', applyFiltersAndSort); // Prevent duplicate listeners
    input.addEventListener('change', applyFiltersAndSort);
  });
}

function renderJobs() {
  jobList.innerHTML = '';
  if (filteredJobs.length === 0) {
    jobList.innerHTML = '<p style="grid-column:1/-1;text-align:center;font-style:italic;color:#ffe560;font-size:1.2rem;">No jobs found matching your criteria.</p>';
    paginationNav.innerHTML = '';
    return;
  }

  const startIdx = (currentPage - 1) * jobsPerPage;
  const jobsToRender = filteredJobs.slice(startIdx, startIdx + jobsPerPage);

  jobsToRender.forEach(job => {
    const card = document.createElement('article');
    card.className = 'job-card';
    card.tabIndex = 0;
    card.setAttribute('role', 'button');
    card.setAttribute('aria-pressed', 'false');
    card.dataset.jobId = job.id;
    const isSaved = savedJobs.has(job.id.toString());
    const tagsHtml = job.categories.map(tag => `<span class="tag">${tag}</span>`).join('');
    card.innerHTML = `
      <div class="job-header">
        <h3 class="job-title">${job.title}</h3>
        <div class="company-name">${job.company}</div>
      </div>
      <div class="job-info">
        <span><strong>Location:</strong> ${job.location}</span>
        <span><strong>Type:</strong> ${job.type}</span>
        <span><strong>Salary:</strong> ${job.salaryText}</span>
      </div>
      <div class="tags">${tagsHtml}</div>
      <button class="btn-save ${isSaved ? 'saved' : ''}" aria-pressed="${isSaved}" aria-label="${isSaved ? 'Unsave job' : 'Save job'}" tabindex="0">${isSaved ? 'Saved' : 'Save Job'}</button>
    `;
    card.addEventListener('click', e => {
      if (e.target.classList.contains('btn-save')) {
        toggleSaveJob(job.id, e.target);
        e.stopPropagation();
        return;
      }
      openJobModal(job);
    });
    card.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if (document.activeElement === card) openJobModal(job);
      }
    });
    const saveBtn = card.querySelector('.btn-save');
    saveBtn.addEventListener('keydown', e => {
      if ((e.key === 'Enter' || e.key === ' ') && document.activeElement === saveBtn) {
        e.preventDefault();
        toggleSaveJob(job.id, saveBtn);
      }
    });
    jobList.appendChild(card);
  });

  renderPagination();
}

function renderPagination() {
  const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
  paginationNav.innerHTML = '';
  if (totalPages <= 1) return;

  let html = '';
  html += `<button ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page" id="pagePrev">«</button>`;
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);
  if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
  for (let i = startPage; i <= endPage; i++) {
    html += `<button class="${i === currentPage ? 'current-page' : ''}" aria-current="${i === currentPage ? 'page' : 'false'}" aria-label="Page ${i}" data-page="${i}">${i}</button>`;
  }
  html += `<button ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page" id="pageNext">»</button>`;
  paginationNav.innerHTML = html;

  document.getElementById('pagePrev').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      renderJobs();
      jobList.focus();
    }
  });
  document.getElementById('pageNext').addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++;
      renderJobs();
      jobList.focus();
    }
  });
  paginationNav.querySelectorAll('button[data-page]').forEach(button => {
    button.addEventListener('click', e => {
      const page = Number(e.target.dataset.page);
      if (page !== currentPage) {
        currentPage = page;
        renderJobs();
        jobList.focus();
      }
    });
  });
}

function openJobModal(job) {
  modalJobTitle.textContent = job.title;
  modalCompany.textContent = job.company;
  modalLocation.textContent = `Location: ${job.location}`;
  modalType.textContent = `Type: ${job.type}`;
  modalSalary.textContent = `Salary: ${job.salaryText}`;
  modalDescription.textContent = job.description;
  modalTags.innerHTML = job.categories.map(c => `<span class="tag">${c}</span>`).join('');
  modalApplyBtn.href = job.applyUrl;
  modalApplyBtn.setAttribute('aria-label', `Apply for ${job.title} at ${job.company}`);
  modalBackdrop.classList.add('active');
  modalBackdrop.setAttribute('aria-hidden', 'false');
  modalCloseBtn.focus();
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
  document.getElementById(modalId).setAttribute('aria-hidden', 'true');
}

function toggleSaveJob(jobId, btn) {
  jobId = String(jobId);
  if (savedJobs.has(jobId)) {
    savedJobs.delete(jobId);
    btn.classList.remove('saved');
    btn.textContent = 'Save Job';
    btn.setAttribute('aria-pressed', 'false');
    btn.setAttribute('aria-label', 'Save job');
    showNotification('Job removed from saved jobs.', 'info');
  } else {
    savedJobs.add(jobId);
    btn.classList.add('saved');
    btn.textContent = 'Saved';
    btn.setAttribute('aria-pressed', 'true');
    btn.setAttribute('aria-label', 'Unsave job');
    showNotification('Job saved successfully!', 'success');
  }
  localStorage.setItem('savedJobs', JSON.stringify(Array.from(savedJobs)));
}

function applyFiltersAndSort() {
  const searchTerm = searchInput.value.trim().toLowerCase();
  const selectedTypes = Array.from(filterTypeInputs).filter(i => i.checked).map(i => i.value);
  const selectedCategories = Array.from(filterCategoryInputs).filter(i => i.checked).map(i => i.value);
  const selectedLocations = Array.from(document.querySelectorAll('#filterLocation input[type=checkbox]')).filter(i => i.checked).map(i => i.value);
  const selectedSalaryRanges = Array.from(filterSalaryInputs).filter(i => i.checked).map(i => i.value);

  filteredJobs = jobs.filter(job => {
    if (searchTerm) {
      const searchTarget = (job.title + ' ' + job.company + ' ' + job.description).toLowerCase();
      if (!searchTarget.includes(searchTerm)) return false;
    }
    if (selectedTypes.length && !selectedTypes.includes(job.type)) return false;
    if (selectedCategories.length && !job.categories.some(c => selectedCategories.includes(c))) return false;
    if (selectedLocations.length && !selectedLocations.includes(job.location)) return false;
    if (selectedSalaryRanges.length) {
      let matchSalary = false;
      for (const range of selectedSalaryRanges) {
        if (range === '25+') {
          if (job.salaryRange[1] >= 25) matchSalary = true;
        } else {
          const [min, max] = range.split('-').map(Number);
          if (
            (job.salaryRange[0] >= min && job.salaryRange[0] <= max) ||
            (job.salaryRange[1] >= min && job.salaryRange[1] <= max) ||
            (job.salaryRange[0] <= min && job.salaryRange[1] >= max)
          ) {
            matchSalary = true;
          }
        }
      }
      if (!matchSalary) return false;
    }
    return true;
  });

  applySort();
  currentPage = 1;
  renderJobs();
}

function applySort() {
  const sortValue = sortSelect.value;
  switch (sortValue) {
    case 'salaryAsc':
      filteredJobs.sort((a, b) => a.salaryRange[0] - b.salaryRange[0]);
      break;
    case 'salaryDesc':
      filteredJobs.sort((a, b) => b.salaryRange[1] - a.salaryRange[1]);
      break;
    case 'locationAsc':
      filteredJobs.sort((a, b) => a.location.localeCompare(b.location));
      break;
    case 'locationDesc':
      filteredJobs.sort((a, b) => b.location.localeCompare(a.location));
      break;
    case 'relevance':
    default:
      filteredJobs = [...jobs].filter(job => filteredJobs.some(fj => fj.id === job.id));
      break;
  }
}

function fetchJobs() {
  fetch('get_jobs.php')
    .then(response => {
      if (!response.ok) throw new Error('Failed to fetch jobs');
      return response.json();
    })
    .then(data => {
      jobs = data;
      filteredJobs = [...jobs];
      populateLocationFilters();
      applyFiltersAndSort();
    })
    .catch(error => {
      console.error('Error fetching jobs:', error);
      jobList.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#ff5600;font-size:1.2rem;">Failed to load jobs. Please try again later.</p>';
      showNotification('Failed to load jobs.', 'error');
    });
}

const onSearchInput = debounce(() => {
  applyFiltersAndSort();
}, 350);

searchInput.addEventListener('input', onSearchInput);
sortSelect.addEventListener('change', applyFiltersAndSort);
filterTypeInputs.forEach(input => input.addEventListener('change', applyFiltersAndSort));
filterCategoryInputs.forEach(input => input.addEventListener('change', applyFiltersAndSort));
filterSalaryInputs.forEach(input => input.addEventListener('change', applyFiltersAndSort));
modalCloseBtn.addEventListener('click', () => closeModal('modalBackdrop'));
modalBackdrop.addEventListener('click', e => {
  if (e.target === modalBackdrop) closeModal('modalBackdrop');
});
document.addEventListener('keydown', e => {
  if ((e.key === 'Escape' || e.key === 'Esc') && modalBackdrop.classList.contains('active')) closeModal('modalBackdrop');
});

btnAll.addEventListener('click', () => {
  btnAll.classList.add('active');
  btnAll.setAttribute('aria-pressed', 'true');
  btnSaved.classList.remove('active');
  btnSaved.setAttribute('aria-pressed', 'false');
  applyFiltersAndSort();
});

btnSaved.addEventListener('click', () => {
  btnSaved.classList.add('active');
  btnSaved.setAttribute('aria-pressed', 'true');
  btnAll.classList.remove('active');
  btnAll.setAttribute('aria-pressed', 'false');
  const savedArray = Array.from(savedJobs).map(Number);
  filteredJobs = jobs.filter(job => savedArray.includes(job.id));
  currentPage = 1;
  renderJobs();
});

btnAddJob.addEventListener('click', () => {
  addJobModalBackdrop.classList.add('active');
  addJobModalBackdrop.setAttribute('aria-hidden', 'false');
  addJobForm.jobTitle.focus();
});

addJobModalCloseBtn.addEventListener('click', () => closeModal('addJobModalBackdrop'));
addJobModalBackdrop.addEventListener('click', e => {
  if (e.target === addJobModalBackdrop) closeModal('addJobModalBackdrop');
});
document.addEventListener('keydown', e => {
  if ((e.key === 'Escape' || e.key === 'Esc') && addJobModalBackdrop.classList.contains('active')) closeModal('addJobModalBackdrop');
});

addJobForm.addEventListener('submit', e => {
  e.preventDefault();
  const job = {
    title: document.getElementById('jobTitle').value,
    company: document.getElementById('jobCompany').value,
    location: document.getElementById('jobLocation').value,
    salary_min: parseFloat(document.getElementById('jobSalaryMin').value),
    salary_max: parseFloat(document.getElementById('jobSalaryMax').value),
    type: document.getElementById('jobType').value,
    categories: document.getElementById('jobCategories').value,
    description: document.getElementById('jobDescription').value,
    url: document.getElementById('jobUrl').value
  };

  fetch('add_job.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(job)
  })
    .then(response => {
      if (!response.ok) throw new Error('Failed to add job');
      return response.json();
    })
    .then(data => {
      if (data.success) {
        fetchJobs();
        closeModal('addJobModalBackdrop');
        addJobForm.reset();
        showNotification('Job added successfully!', 'success');
      } else {
        throw new Error(data.error || 'Unknown error');
      }
    })
    .catch(error => {
      console.error('Error adding job:', error);
      showNotification('Failed to add job: ' + error.message, 'error');
    });
});

// Inisialisasi
fetchJobs();