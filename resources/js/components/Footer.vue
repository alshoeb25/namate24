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
