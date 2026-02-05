<template>
  <div class="bg-rose-50 min-h-screen py-6 sm:py-8 md:py-12 px-3 sm:px-4 md:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <header class="text-center mb-6 sm:mb-8 md:mb-10">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-rose-600">Safety Documents</h1>
        <div class="mt-2 sm:mt-3 h-1 w-20 sm:w-24 mx-auto rounded-full bg-gradient-to-r from-pink-400 to-rose-600"></div>
      </header>

      <section class="space-y-4 sm:space-y-6 md:space-y-8">
        <article class="bg-white rounded-xl sm:rounded-2xl shadow-lg border-t-4 border-rose-500 p-4 sm:p-6 md:p-8">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 text-sm sm:text-base">
              <i class="fas fa-shield-alt"></i>
            </span>
            <h2 class="text-lg sm:text-xl font-semibold text-rose-600">Personal Safety</h2>
          </div>
          <ul class="list-disc list-inside space-y-2 text-sm sm:text-base text-gray-700">
            <li>Meet in public places for the first session whenever possible.</li>
            <li>Share session details with a trusted family member or friend.</li>
            <li>Verify tutor credentials and profiles before scheduling.</li>
            <li>Keep communication within the platform for transparency.</li>
          </ul>
        </article>

        <article class="bg-white rounded-xl sm:rounded-2xl shadow-lg border-t-4 border-rose-500 p-4 sm:p-6 md:p-8">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 text-sm sm:text-base">
              <i class="fas fa-user-check"></i>
            </span>
            <h2 class="text-lg sm:text-xl font-semibold text-rose-600">Verification & Conduct</h2>
          </div>
          <ul class="list-disc list-inside space-y-2 text-sm sm:text-base text-gray-700">
            <li>Review tutor ratings, reviews, and profile history.</li>
            <li>Report any suspicious behavior to support immediately.</li>
            <li>Maintain respectful communication at all times.</li>
          </ul>
        </article>

        <article class="bg-white rounded-xl sm:rounded-2xl shadow-lg border-t-4 border-rose-500 p-4 sm:p-6 md:p-8">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 text-sm sm:text-base">
              <i class="fas fa-credit-card"></i>
            </span>
            <h2 class="text-lg sm:text-xl font-semibold text-rose-600">Payments & Records</h2>
          </div>
          <ul class="list-disc list-inside space-y-2 text-sm sm:text-base text-gray-700">
            <li>Use the platform for payments whenever available.</li>
            <li>Keep receipts or confirmations for every transaction.</li>
            <li>Avoid sharing sensitive payment details over chat.</li>
          </ul>
        </article>

        <article class="bg-white rounded-xl sm:rounded-2xl shadow-lg border-t-4 border-rose-500 p-4 sm:p-6 md:p-8">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 text-sm sm:text-base">
              <i class="fas fa-headset"></i>
            </span>
            <h2 class="text-lg sm:text-xl font-semibold text-rose-600">Support</h2>
          </div>
          <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
            If you experience any issues, contact support immediately. We are committed to helping you resolve concerns quickly and safely.
          </p>
          <div class="mt-4">
            <router-link to="/contact-us" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition text-sm font-medium">
              <i class="fas fa-envelope mr-2"></i>Contact Support
            </router-link>
          </div>
        </article>

        <article class="bg-white rounded-xl sm:rounded-2xl shadow-lg border-t-4 border-rose-500 p-4 sm:p-6 md:p-8">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 text-sm sm:text-base">
              <i class="fas fa-file-contract"></i>
            </span>
            <h2 class="text-lg sm:text-xl font-semibold text-rose-600">Terms & Conditions</h2>
          </div>
          <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
            Review our Terms and Conditions for platform-wide policies and user responsibilities.
          </p>
          <div class="mt-4">
            <router-link to="/terms-and-conditions" class="inline-flex items-center px-4 py-2 bg-white text-rose-700 border border-rose-300 rounded-lg hover:bg-rose-50 transition text-sm font-medium">
              <i class="fas fa-external-link-alt mr-2"></i>View Terms and Conditions
            </router-link>
          </div>
        </article>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SafetyDocuments'
};
</script>
