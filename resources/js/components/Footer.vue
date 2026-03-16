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
          <li><router-link :to="`/data-scientist-tutors`" class="hover:text-blue-400 transition">Data Scientist</router-link></li>
          <li><router-link :to="`/full-stack-web-developer-tutors`" class="hover:text-blue-400 transition">Full Stack Web Developer</router-link></li>
          <li><router-link :to="`/cloud-engineer-tutors`" class="hover:text-blue-400 transition">Cloud Engineer</router-link></li>
          <li><router-link :to="`/project-manager-tutors`" class="hover:text-blue-400 transition">Project Manager</router-link></li>
          <li><router-link :to="`/game-developer-tutors`" class="hover:text-blue-400 transition">Game Developer</router-link></li>
          <li><router-link to="/tutors" class="hover:text-blue-400 transition">All Career Accelerators</router-link></li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">Web Development</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li><router-link :to="`/web-development-tutors`" class="hover:text-blue-400 transition">Web Development</router-link></li>
          <li><router-link :to="`/javascript-tutors`" class="hover:text-blue-400 transition">JavaScript</router-link></li>
          <li><router-link :to="`/react-js-tutors`" class="hover:text-blue-400 transition">React JS</router-link></li>
          <li><router-link :to="`/angular-tutors`" class="hover:text-blue-400 transition">Angular</router-link></li>
          <li><router-link :to="`/java-tutors`" class="hover:text-blue-400 transition">Java</router-link></li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">IT Certifications</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li><router-link :to="`/amazon-aws-tutors`" class="hover:text-blue-400 transition">Amazon AWS</router-link></li>
          <li><router-link :to="`/aws-cloud-practitioner-tutors`" class="hover:text-blue-400 transition">AWS Cloud Practitioner</router-link></li>
          <li><router-link :to="`/azure-fundamentals-tutors`" class="hover:text-blue-400 transition">Azure Fundamentals</router-link></li>
          <li><router-link :to="`/aws-solutions-architect-tutors`" class="hover:text-blue-400 transition">AWS Solutions Architect</router-link></li>
          <li><router-link :to="`/kubernetes-tutors`" class="hover:text-blue-400 transition">Kubernetes</router-link></li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-3">Leadership</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li><router-link :to="`/leadership-tutors`" class="hover:text-blue-400 transition">Leadership</router-link></li>
          <li><router-link :to="`/management-skills-tutors`" class="hover:text-blue-400 transition">Management Skills</router-link></li>
          <li><router-link :to="`/project-management-tutors`" class="hover:text-blue-400 transition">Project Management</router-link></li>
          <li><router-link :to="`/personal-productivity-tutors`" class="hover:text-blue-400 transition">Personal Productivity</router-link></li>
          <li><router-link :to="`/emotional-intelligence-tutors`" class="hover:text-blue-400 transition">Emotional Intelligence</router-link></li>
        </ul>
      </div>

      <!-- 🆕 NEW SCHOOLING SECTION -->
      <div>
        <h3 class="font-semibold mb-3">Schooling</h3>
        <ul class="space-y-2 text-gray-300 text-sm">
          <li><router-link :to="`/mathematics-tutors`" class="hover:text-blue-400 transition">Mathematics</router-link></li>
          <li><router-link :to="`/science-physics-chemistry-biology-tutors`" class="hover:text-blue-400 transition">Science (Physics / Chemistry / Biology)</router-link></li>
          <li><router-link :to="`/english-tutors`" class="hover:text-blue-400 transition">English</router-link></li>
          <li><router-link :to="`/social-studies-tutors`" class="hover:text-blue-400 transition">Social Studies</router-link></li>
          <li><router-link :to="`/computer-science-tutors`" class="hover:text-blue-400 transition">Computer Science</router-link></li>
          <li><router-link :to="`/general-knowledge-tutors`" class="hover:text-blue-400 transition">General Knowledge</router-link></li>
        </ul>
      </div>

    </div>


    <!-- MOBILE ACCORDION (NO BORDER-T HERE) -->
    <div class="lg:hidden space-y-3">
      <details class="py-3">
        <summary class="cursor-pointer font-semibold">In-demand Careers</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li><router-link :to="`/data-scientist-tutors`" class="hover:text-blue-400 transition">Data Scientist</router-link></li>
          <li><router-link :to="`/full-stack-web-developer-tutors`" class="hover:text-blue-400 transition">Full Stack Web Developer</router-link></li>
          <li><router-link :to="`/cloud-engineer-tutors`" class="hover:text-blue-400 transition">Cloud Engineer</router-link></li>
          <li><router-link :to="`/project-manager-tutors`" class="hover:text-blue-400 transition">Project Manager</router-link></li>
          <li><router-link :to="`/game-developer-tutors`" class="hover:text-blue-400 transition">Game Developer</router-link></li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Web Development</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li><router-link :to="`/web-development-tutors`" class="hover:text-blue-400 transition">Web Development</router-link></li>
          <li><router-link :to="`/javascript-tutors`" class="hover:text-blue-400 transition">JavaScript</router-link></li>
          <li><router-link :to="`/react-js-tutors`" class="hover:text-blue-400 transition">React JS</router-link></li>
          <li><router-link :to="`/angular-tutors`" class="hover:text-blue-400 transition">Angular</router-link></li>
          <li><router-link :to="`/java-tutors`" class="hover:text-blue-400 transition">Java</router-link></li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">IT Certifications</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li><router-link :to="`/amazon-aws-tutors`" class="hover:text-blue-400 transition">Amazon AWS</router-link></li>
          <li><router-link :to="`/aws-cloud-practitioner-tutors`" class="hover:text-blue-400 transition">AWS Cloud Practitioner</router-link></li>
          <li><router-link :to="`/azure-fundamentals-tutors`" class="hover:text-blue-400 transition">Azure Fundamentals</router-link></li>
          <li><router-link :to="`/aws-solutions-architect-tutors`" class="hover:text-blue-400 transition">AWS Solutions Architect</router-link></li>
          <li><router-link :to="`/kubernetes-tutors`" class="hover:text-blue-400 transition">Kubernetes</router-link></li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Leadership</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li><router-link :to="`/leadership-tutors`" class="hover:text-blue-400 transition">Leadership</router-link></li>
          <li><router-link :to="`/management-skills-tutors`" class="hover:text-blue-400 transition">Management Skills</router-link></li>
          <li><router-link :to="`/project-management-tutors`" class="hover:text-blue-400 transition">Project Management</router-link></li>
          <li><router-link :to="`/personal-productivity-tutors`" class="hover:text-blue-400 transition">Personal Productivity</router-link></li>
          <li><router-link :to="`/emotional-intelligence-tutors`" class="hover:text-blue-400 transition">Emotional Intelligence</router-link></li>
        </ul>
      </details>

      <details class="py-3">
        <summary class="cursor-pointer font-semibold">Schooling</summary>
        <ul class="mt-3 text-gray-300 text-sm space-y-2 pl-3">
          <li><router-link :to="`/mathematics-tutors`" class="hover:text-blue-400 transition">Mathematics</router-link></li>
          <li><router-link :to="`/science-physics-chemistry-biology-tutors`" class="hover:text-blue-400 transition">Science (Physics / Chemistry / Biology)</router-link></li>
          <li><router-link :to="`/english-tutors`" class="hover:text-blue-400 transition">English</router-link></li>
          <li><router-link :to="`/social-studies-tutors`" class="hover:text-blue-400 transition">Social Studies</router-link></li>
          <li><router-link :to="`/computer-science-tutors`" class="hover:text-blue-400 transition">Computer Science</router-link></li>
          <li><router-link :to="`/general-knowledge-tutors`" class="hover:text-blue-400 transition">General Knowledge</router-link></li>
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
        <div class="flex items-center gap-3">
          <!-- WhatsApp -->
          <a href="https://whatsapp.com/channel/0029VbCaiMqEQIavdVfcPP3D" target="_blank" rel="noopener noreferrer"
            class="flex items-center gap-2 bg-green-500 hover:bg-green-400 text-white text-sm font-medium px-3 py-2 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
          </a>
          <!-- Facebook -->
          <a href="https://www.facebook.com/share/17w6Yuaj9n/" target="_blank" rel="noopener noreferrer"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-3 py-2 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
              <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
            </svg>
          </a>
          <!-- Instagram -->
          <a href="https://www.instagram.com/namate24_tutorconnect?igsh=MTl2djB5dWltdjdhYg==" target="_blank" rel="noopener noreferrer"
            class="flex items-center gap-2 text-white text-sm font-medium px-3 py-2 rounded-full transition"
            style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
            </svg>
          </a>
        </div>
        <p class="text-gray-400 text-sm">© 2026 Namate 24, Inc.</p>
      </div>
    </div>
  </footer>

</template>

<script>
import { useRouter } from 'vue-router';

export default {
  name: 'Footer',
  setup() {
    const router = useRouter();

    const scrollToTop = () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const navigateToTutors = (subject) => {
      const slug = subject.toLowerCase().replace(/\s+/g, '-').replace(/[()\/&]/g, '');
      scrollToTop();
      router.push(`/${slug}-tutors`);
    };

    // Listen to route changes and scroll to top
    router.afterEach(() => {
      scrollToTop();
    });

    return { navigateToTutors, scrollToTop };
  }
};
</script>
