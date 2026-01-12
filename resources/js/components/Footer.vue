<template>
  <footer class="w-full bg-gradient-to-r from-gray-900 to-gray-800 text-white pt-10 pb-6 px-5 lg:px-20">
    
    <!-- TOP COMPANIES + LOGO -->
    <div class="mb-10">
      <div class="flex justify-between items-center mb-5">
        <p class="text-gray-300 font-semibold">
          Top companies choose <span class="text-blue-400">Namate 24 Business</span>
          to build in-demand career skills.
        </p>

        <img src="https://image2url.com/images/1765218804774-6a3b1d8d-9464-463a-80a1-d32acc620cfc.png"
          class="h-10 object-contain" alt="Companies">
      </div>

      <!-- SINGLE LINE -->
      <div class="border-t border-gray-700"></div>
    </div>

    <!-- DESKTOP GRID -->
    <div class="hidden lg:grid grid-cols-6 gap-10">

      <div>
        <h3 class="font-semibold mb-3">In-demand Careers</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li>Data Scientist</li>
          <li>Full Stack Web Developer</li>
          <li>Cloud Engineer</li>
          <li>Project Manager</li>
          <li>Game Developer</li>
          <li>All Career Accelerators</li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">Web Development</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li>Web Development</li>
          <li>JavaScript</li>
          <li>React JS</li>
          <li>Angular</li>
          <li>Java</li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">IT Certifications</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li>Amazon AWS</li>
          <li>AWS Cloud Practitioner</li>
          <li>Azure Fundamentals</li>
          <li>AWS Solutions Architect</li>
          <li>Kubernetes</li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">Leadership</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li>Leadership</li>
          <li>Management Skills</li>
          <li>Project Management</li>
          <li>Personal Productivity</li>
          <li>Emotional Intelligence</li>
        </ul>
      </div>

      <!-- 🆕 NEW SCHOOLING SECTION -->
      <div>
        <h3 class="font-semibold mb-3">Schooling</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li>Mathematics</li>
          <li>Science (Physics / Chemistry / Biology)</li>
          <li>English</li>
          <li>Social Studies</li>
          <li>Computer Science</li>
          <li>General Knowledge</li>
        </ul>
      </div>

    </div>


    <!-- MOBILE ACCORDION (NO BORDER-T HERE) -->
    <div class="lg:hidden space-y-3">
      <details class="py-3">
        <summary class="cursor-pointer font-semibold">In-demand Careers</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>Data Scientist</li>
          <li>Full Stack Web Developer</li>
          <li>Cloud Engineer</li>
          <li>Project Manager</li>
          <li>Game Developer</li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Certifications by Skill</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>Cybersecurity Certification</li>
          <li>Project Management</li>
          <li>Cloud Certification</li>
          <li>Data Analytics</li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Web Development</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>JavaScript</li>
          <li>React</li>
          <li>Angular</li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">IT Certifications</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>AWS</li>
          <li>Azure</li>
          <li>Kubernetes</li>
        </ul>
      </details>


      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Leadership</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>Management Skills</li>
          <li>Productivity</li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Schooling</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li>Mathematics</li>
          <li>Science (Physics / Chemistry / Biology)</li>
          <li>English</li>
          <li>Social Studies</li>
          <li>Computer Science</li>
          <li>General Knowledge</li>
        </ul>
      </details>
    </div>

    <!-- BOTTOM AREA (SINGLE LINE TOP) -->
    <div class="border-t border-gray-700 mt-10 pt-6 flex flex-col lg:flex-row justify-between items-start lg:items-center">

      <div class="flex gap-10 mb-6 lg:mb-0">
        <ul class="space-y-2 text-sm text-gray-300">
          <li class="font-semibold text-white">About</li>
          <li><router-link to="/contact-us" class="hover:text-white transition">Contact us</router-link></li>
        </ul>

        <ul class="space-y-2 text-sm text-gray-300">
          <li class="font-semibold text-white">Legal & Accessibility</li>
          <li><router-link to="/privacy-policy" class="hover:text-white transition">Privacy Policy</router-link></li>
          <li><router-link to="/terms-and-conditions" class="hover:text-white transition">Terms &amp; Conditions</router-link></li>
          <li><router-link to="/refund-policy" class="hover:text-white transition">Refund Policy</router-link></li>
        </ul>
      </div>

      <div class="flex flex-col items-start gap-3">
        <img src="https://image2url.com/images/1765253042333-dd88da98-ff0a-47d6-b112-9bda33ebbc3c.png" class="h-8" alt="Namate 24">
        <p class="text-gray-400 text-sm">© 2026 Namate 24, Inc.</p>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer'
};
</script>
