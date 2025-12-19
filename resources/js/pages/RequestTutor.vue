<template>
  <div class="min-h-screen bg-gray-50 py-4 md:py-8">
    <div class="max-w-5xl mx-auto px-4">
      <!-- Back Navigation -->
      <div class="mb-6 md:mb-8">
        <button @click="goBack" 
                class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium transition-colors">
          <i class="fas fa-arrow-left"></i>
          <span>Back to My Requirements</span>
        </button>
      </div>

      <!-- Header -->
      <div class="mb-6 md:mb-10">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-user-plus mr-2 text-blue-600"></i>{{ isEditMode ? 'Edit' : 'Request A' }} Tutor
        </h1>
        <p class="text-gray-600">{{ isEditMode ? 'Update your requirements' : "Fill in your requirements and we'll connect you with the perfect tutor" }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="showSuccess" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center gap-3">
          <i class="fas fa-check-circle text-green-600 text-xl"></i>
          <div>
            <p class="font-medium text-green-800">Requirement {{ isEditMode ? 'updated' : 'submitted' }} successfully!</p>
            <p class="text-green-600 text-sm">{{ isEditMode ? 'Your changes have been saved.' : 'Tutors will contact you soon.' }}</p>
          </div>
        </div>
      </div>

      <!-- Desktop Stepper -->
      <div class="hidden md:flex items-center justify-between mb-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div v-for="(section, index) in sections" :key="index + 1" class="flex-1 flex items-center">
          <button @click="currentSection = index + 1" type="button" class="flex items-center">
            <!-- Circle -->
            <div
              :class="[
                'w-12 h-12 flex items-center justify-center rounded-full border-2 text-sm font-semibold transition-all cursor-pointer',
                isSectionCompleted(index + 1) ? 'bg-green-500 text-white border-green-500' :
                (index + 1) === currentSection ? 'border-blue-600 text-blue-600 bg-blue-50' :
                'border-gray-300 text-gray-400'
              ]">
              <i v-if="isSectionCompleted(index + 1)" class="fas fa-check"></i>
              <span v-else>{{ index + 1 }}</span>
            </div>

            <!-- Label -->
            <div class="ml-3 text-left">
              <div class="text-sm font-semibold"
                :class="(index + 1) === currentSection ? 'text-blue-600' : isSectionCompleted(index + 1) ? 'text-green-600' : 'text-gray-500'">
                {{ section.title }}
              </div>
              <div class="text-xs text-gray-400">{{ section.steps.length }} fields</div>
            </div>
          </button>

          <!-- Line -->
          <div v-if="index !== sections.length - 1" 
               :class="[
                 'flex-1 h-0.5 mx-4 transition-all',
                 isSectionCompleted(index + 1) ? 'bg-green-500' : 'bg-gray-200'
               ]"></div>
        </div>
      </div>

      <!-- Mobile Progress Bar -->
      <div class="md:hidden mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex justify-between text-sm mb-3">
          <span class="font-semibold text-gray-800">Section {{ currentSection }} of {{ sections.length }}</span>
          <span class="text-blue-600 font-medium">{{ completionPercentage }}%</span>
        </div>
        
        <div class="mb-3">
          <p class="text-sm text-gray-600 font-medium">{{ sections[currentSection - 1].title }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ sections[currentSection - 1].steps.length }} fields</p>
        </div>

        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
          <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500"
            :style="{ width: (currentSection / sections.length) * 100 + '%' }">
          </div>
        </div>
        
        <!-- Mobile Section Selector -->
        <div class="flex gap-2 mt-4">
          <button v-for="(section, index) in sections" :key="index"
                  @click="currentSection = index + 1"
                  type="button"
                  :class="[
                    'flex-1 px-3 py-2 rounded-lg text-xs font-medium transition-all',
                    currentSection === index + 1 ? 'bg-blue-600 text-white' : 
                    isSectionCompleted(index + 1) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                  ]">
            {{ index + 1 }}
          </button>
        </div>
      </div>

      <!-- Form Steps -->
      <form @submit.prevent="submitRequest" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 lg:p-8 mb-6">
        
        <!-- Section 1: Basic Information -->
        <div v-if="currentSection === 1" class="space-y-8">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
              <i class="fas fa-info-circle mr-2 text-blue-600"></i>Basic Information
            </h2>
            <p class="text-gray-600">Tell us where you are and how to reach you</p>
          </div>

          <!-- Location -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>Location
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">City<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Where do you need tutoring?</p>
              </div>
              <div class="md:col-span-3">
                <input v-model="form.city" 
                       type="text" 
                       placeholder="Enter your city"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Area/Locality<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Your neighborhood</p>
              </div>
              <div class="md:col-span-3">
                <input v-model="form.area" 
                       type="text" 
                       placeholder="Enter your area"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">PIN Code</label>
                <p class="text-gray-500 text-sm">Optional</p>
              </div>
              <div class="md:col-span-3">
                <input v-model="form.pincode" 
                       type="text" 
                       placeholder="Enter PIN code"
                       maxlength="6"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-phone text-blue-600"></i>Contact Information
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <p class="text-gray-500 text-sm">Tutors will contact you</p>
              </div>
              <div class="md:col-span-3">
                <div class="flex gap-3">
                  <input type="text" value="+91" disabled
                         class="w-16 border border-gray-300 rounded-lg px-3 py-3 bg-gray-100 text-center font-medium">
                  <input v-model="phoneNumber" @input="formatPhoneNumber"
                         type="tel" placeholder="9876543210" maxlength="10"
                         class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
              </div>
            </div>

            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Alternate Phone</label>
                <p class="text-gray-500 text-sm">Optional</p>
              </div>
              <div class="md:col-span-3">
                <div class="flex gap-3">
                  <input type="text" value="+91" disabled
                         class="w-16 border border-gray-300 rounded-lg px-3 py-3 bg-gray-100 text-center font-medium">
                  <input v-model="alternatePhoneNumber" @input="formatAlternatePhone"
                         type="tel" placeholder="9876543210" maxlength="10"
                         class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 2: Requirement Details -->
        <div v-if="currentSection === 2" class="space-y-8">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
              <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Requirement Details
            </h2>
            <p class="text-gray-600">Tell us what you need and your learning goals</p>
          </div>

          <!-- Details -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-file-alt text-blue-600"></i>About You
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Student Name<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Who needs tutoring?</p>
              </div>
              <div class="md:col-span-3">
                <input v-model="form.student_name" 
                       type="text" 
                       placeholder="Enter student name"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>

            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Details</label>
                <p class="text-gray-500 text-sm">Describe your needs</p>
              </div>
              <div class="md:col-span-3">
                <textarea v-model="form.description" 
                          rows="6"
                          placeholder="Describe your learning goals, current level, any specific requirements..."
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>Don't share contact details here</p>
              </div>
            </div>
          </div>

          <!-- Subjects -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-book text-blue-600"></i>Subjects
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Subjects</label>
                <p class="text-gray-500 text-sm">What do you want to learn?</p>
              </div>
              <div class="md:col-span-3">
                <div class="relative mb-3">
                  <input v-model="subjectSearch" @focus="showSubjectDropdown = true"
                         type="text" placeholder="Search subjects..."
                         class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <i class="fas fa-search absolute right-3 top-4 text-gray-400"></i>
                  
                  <div v-if="showSubjectDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <div v-for="subject in filteredSubjects" :key="subject.id"
                         @click="toggleSubject(subject.name)"
                         class="px-4 py-2 hover:bg-blue-50 cursor-pointer flex items-center justify-between">
                      <span>{{ subject.name }}</span>
                      <i v-if="form.subjects.includes(subject.name)" class="fas fa-check text-blue-600"></i>
                    </div>
                    <div v-if="filteredSubjects.length === 0" class="px-4 py-3 text-gray-500 text-sm">No subjects found</div>
                  </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mb-3">
                  <span v-for="subject in form.subjects" :key="subject"
                        class="bg-blue-100 text-blue-800 px-3 py-1.5 rounded-full text-sm flex items-center gap-2">
                    {{ subject }}
                    <button @click="removeSubject(subject)" type="button" class="text-blue-600 hover:text-blue-800">
                      <i class="fas fa-times text-xs"></i>
                    </button>
                  </span>
                </div>
                
                <div class="flex gap-2">
                  <input v-model="customSubject" type="text" placeholder="Add custom subject"
                         @keyup.enter="addCustomSubject"
                         class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <button @click="addCustomSubject" type="button"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Level -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-graduation-cap text-blue-600"></i>Level
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Your Level</label>
                <p class="text-gray-500 text-sm">Current knowledge level</p>
              </div>
              <div class="md:col-span-3">
                <select v-model="form.level" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                  <option value="">Select your level</option>
                  <option v-for="level in levelOptions" :key="level.id" :value="level.name">{{ level.name }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Service Type -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-clipboard-check text-blue-600"></i>Service Type
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">I Want<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Type of help needed</p>
              </div>
              <div class="md:col-span-3">
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.service_type === 'tutoring' }">
                    <input type="radio" value="tutoring" v-model="form.service_type" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">Tutoring</span>
                      <p class="text-sm text-gray-600">Regular classes and teaching</p>
                    </div>
                  </label>
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.service_type === 'assignment_help' }">
                    <input type="radio" value="assignment_help" v-model="form.service_type" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">Assignment Help</span>
                      <p class="text-sm text-gray-600">Help with assignments and projects</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Logistics & Preferences -->
        <div v-if="currentSection === 3" class="space-y-8">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
              <i class="fas fa-sliders-h mr-2 text-blue-600"></i>Logistics & Preferences
            </h2>
            <p class="text-gray-600">Final details about schedule, budget, and preferences</p>
          </div>

          <!-- Meeting Options -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-handshake text-blue-600"></i>Meeting Options
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">How to meet?<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Select all that apply</p>
              </div>
              <div class="md:col-span-3">
                <div class="space-y-3">
                  <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.meeting_options.includes('online') }">
                    <input type="checkbox" value="online" v-model="form.meeting_options"
                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                    <div>
                      <span class="font-medium text-gray-700">Online</span>
                      <p class="text-sm text-gray-600">Video call or online platform</p>
                    </div>
                  </label>
                  <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.meeting_options.includes('at_my_place') }">
                    <input type="checkbox" value="at_my_place" v-model="form.meeting_options"
                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                    <div>
                      <span class="font-medium text-gray-700">At My Place</span>
                      <p class="text-sm text-gray-600">Tutor visits your location</p>
                    </div>
                  </label>
                  <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.meeting_options.includes('travel_to_tutor') }">
                    <input type="checkbox" value="travel_to_tutor" v-model="form.meeting_options"
                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                    <div>
                      <span class="font-medium text-gray-700">Travel to Tutor</span>
                      <p class="text-sm text-gray-600">Visit tutor's location</p>
                    </div>
                  </label>
                </div>
                
                <div v-if="form.meeting_options.includes('travel_to_tutor')" class="mt-4 flex items-center gap-4">
                  <input v-model="form.travel_distance" type="number" min="0" max="50"
                         placeholder="Distance in km"
                         class="w-32 border border-gray-300 rounded-lg px-4 py-3 text-center">
                  <span class="text-gray-600">km travel distance</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Budget -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-rupee-sign text-blue-600"></i>Budget
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Your Budget<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">What's your budget?</p>
              </div>
              <div class="md:col-span-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm text-gray-600 mb-2">Amount (INR)</label>
                    <input v-model="form.budget_amount" type="number" min="0" placeholder="Enter amount"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm text-gray-600 mb-2">Budget Type</label>
                    <select v-model="form.budget_type" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                      <option value="">Select type</option>
                      <option value="fixed">Fixed/Flat</option>
                      <option value="per_hour">Per Hour</option>
                      <option value="per_day">Per Day</option>
                      <option value="per_week">Per Week</option>
                      <option value="per_month">Per Month</option>
                      <option value="per_year">Per Year</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Gender Preference -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-venus-mars text-blue-600"></i>Gender Preference
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Tutor Gender</label>
                <p class="text-gray-500 text-sm">Optional</p>
              </div>
              <div class="md:col-span-3">
                <div class="space-y-3">
                  <label v-for="option in genderOptions" :key="option.value" 
                         class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.gender_preference === option.value }">
                    <input type="radio" :value="option.value" v-model="form.gender_preference" class="text-blue-600">
                    <span class="font-medium text-gray-700">{{ option.label }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Availability -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-clock text-blue-600"></i>Availability
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Time Commitment<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">When do you need help?</p>
              </div>
              <div class="md:col-span-3">
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.availability === 'part_time' }">
                    <input type="radio" value="part_time" v-model="form.availability" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">Part Time</span>
                      <p class="text-sm text-gray-600">Few hours per week</p>
                    </div>
                  </label>
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.availability === 'full_time' }">
                    <input type="radio" value="full_time" v-model="form.availability" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">Full Time</span>
                      <p class="text-sm text-gray-600">Regular daily classes</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Languages -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-language text-blue-600"></i>Languages
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Languages</label>
                <p class="text-gray-500 text-sm">You can communicate in</p>
              </div>
              <div class="md:col-span-3">
                <div class="relative mb-3">
                  <input v-model="languageSearch" @focus="showLanguageDropdown = true"
                         type="text" placeholder="Search languages..."
                         class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <i class="fas fa-search absolute right-3 top-4 text-gray-400"></i>
                  
                  <div v-if="showLanguageDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <div v-for="lang in filteredLanguages" :key="lang"
                         @click="toggleLanguage(lang)"
                         class="px-4 py-2 hover:bg-blue-50 cursor-pointer flex items-center justify-between">
                      <span>{{ lang }}</span>
                      <i v-if="form.languages.includes(lang)" class="fas fa-check text-blue-600"></i>
                    </div>
                  </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                  <span v-for="lang in form.languages" :key="lang"
                        class="bg-gray-100 text-gray-800 px-3 py-1.5 rounded-full text-sm flex items-center gap-2">
                    {{ lang }}
                    <button @click="removeLanguage(lang)" type="button" class="text-gray-500 hover:text-gray-700">
                      <i class="fas fa-times text-xs"></i>
                    </button>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Location Preference -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-800 pb-3 border-b flex items-center gap-2">
              <i class="fas fa-globe text-blue-600"></i>Tutor Location
            </h3>
            
            <div class="grid md:grid-cols-4 gap-4 md:gap-6">
              <div class="md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Get Tutors From<span class="text-red-500">*</span></label>
                <p class="text-gray-500 text-sm">Location preference</p>
              </div>
              <div class="md:col-span-3">
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.tutor_location === 'all_countries' }">
                    <input type="radio" value="all_countries" v-model="form.tutor_location" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">All Countries</span>
                      <p class="text-sm text-gray-600">Accept tutors from anywhere</p>
                    </div>
                  </label>
                  <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition"
                         :class="{ 'bg-blue-100 border-blue-500': form.tutor_location === 'india_only' }">
                    <input type="radio" value="india_only" v-model="form.tutor_location" class="text-blue-600">
                    <div>
                      <span class="font-medium text-gray-700">In India</span>
                      <p class="text-sm text-gray-600">Only tutors from India</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between pt-6 border-t mt-8">
          <button v-if="currentSection > 1"
                  @click="currentSection--" 
                  type="button"
                  class="flex items-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition">
            <i class="fas fa-arrow-left"></i>Previous
          </button>
          <div v-else></div>

          <button v-if="currentSection < 3"
                  @click="currentSection++" 
                  type="button"
                  class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-sm hover:shadow-md">
            Continue<i class="fas fa-arrow-right"></i>
          </button>
          
          <button v-else 
                  @click="submitRequest" 
                  type="button"
                  :disabled="submitting || !allRequiredFieldsFilled"
                  :class="[
                    'flex items-center gap-2 px-6 py-3 rounded-lg font-semibold transition shadow-sm hover:shadow-md',
                    allRequiredFieldsFilled 
                      ? 'bg-green-600 hover:bg-green-700 text-white' 
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]">
            <i class="fas fa-check mr-2"></i>{{ submitting ? 'Submitting...' : 'Submit Request' }}
          </button>
        </div>

      </form>

      <!-- Errors -->
      <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useUserStore } from '../store';
import axios from '../bootstrap';

export default {
  name: 'RequestTutor',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const userStore = useUserStore();
    const currentSection = ref(1);
    const submitting = ref(false);
    const errorMessage = ref('');
    const showSuccess = ref(false);
    const isEditMode = ref(false);
    const requirementId = ref(null);

    // Phone handling
    const phoneNumber = ref('');
    const alternatePhoneNumber = ref('');
    
    // Subject handling
    const subjectOptions = ref([]);
    const subjectSearch = ref('');
    const showSubjectDropdown = ref(false);
    const customSubject = ref('');
    
    // Level handling
    const levelOptions = ref([]);
    
    // Language handling
    const languageOptions = ref([
      'English', 'Hindi', 'Bengali', 'Telugu', 'Marathi', 'Tamil', 
      'Gujarati', 'Kannada', 'Malayalam', 'Punjabi', 'Urdu', 'Odia',
      'Assamese', 'Maithili', 'Sanskrit', 'Konkani', 'Nepali', 'Sindhi'
    ]);
    const languageSearch = ref('');
    const showLanguageDropdown = ref(false);

    const form = reactive({
      student_id: null,
      city: '',
      area: '',
      pincode: '',
      phone: '',
      alternate_phone: '',
      student_name: '',
      description: '',
      subjects: [],
      other_subject: '',
      level: '',
      service_type: '',
      meeting_options: [],
      travel_distance: '',
      budget_amount: '',
      budget_type: '',
      gender_preference: '',
      availability: '',
      languages: [],
      tutor_location: ''
    });

    const sections = [
      { 
        title: 'Basic Information',
        steps: ['Location', 'Contact']
      },
      { 
        title: 'Requirement Details',
        steps: ['Details', 'Subject', 'Level', 'Service Type']
      },
      { 
        title: 'Logistics & Preferences',
        steps: ['Meeting', 'Budget', 'Gender Preference', 'Availability', 'Languages', 'Location Preference']
      }
    ];

    // Check if section is completed
    const isSectionCompleted = (section) => {
      switch(section) {
        case 1: // Basic Information
          return !!(form.city && form.area && form.phone);
        case 2: // Requirement Details
          return !!(form.student_name && form.subjects.length > 0 && form.level && form.service_type);
        case 3: // Logistics & Preferences
          return !!(form.meeting_options.length > 0 && form.budget_amount && form.budget_type && 
                   form.gender_preference && form.availability && form.languages.length > 0 && form.tutor_location);
        default:
          return false;
      }
    };

    // Calculate completion percentage
    const completedSectionsCount = computed(() => {
      let count = 0;
      for (let i = 1; i <= 3; i++) {
        if (isSectionCompleted(i)) count++;
      }
      return count;
    });

    const completionPercentage = computed(() => {
      return Math.round((completedSectionsCount.value / 3) * 100);
    });

    // Check if all required fields are filled
    const allRequiredFieldsFilled = computed(() => {
      return isSectionCompleted(1) && isSectionCompleted(2) && isSectionCompleted(3);
    });

    // Computed for filtered subjects
    const filteredSubjects = computed(() => {
      if (!subjectSearch.value) return subjectOptions.value;
      return subjectOptions.value.filter(s => 
        s.name.toLowerCase().includes(subjectSearch.value.toLowerCase())
      );
    });

    // Computed for filtered languages  
    const filteredLanguages = computed(() => {
      if (!languageSearch.value) return languageOptions.value;
      return languageOptions.value.filter(l => 
        l.toLowerCase().includes(languageSearch.value.toLowerCase())
      );
    });

    const genderOptions = [
      { value: 'no_preference', label: 'No Preference' },
      { value: 'preferably_male', label: 'Preferably Male' },
      { value: 'preferably_female', label: 'Preferably Female' },
      { value: 'only_male', label: 'Only Male' },
      { value: 'only_female', label: 'Only Female' }
    ];

    // Fetch data from database
    const fetchFormData = async () => {
      try {
        // Fetch subjects
        const subjectsRes = await axios.get('/api/subjects');
        subjectOptions.value = subjectsRes.data;
        
        // Fetch levels
        const levelsRes = await axios.get('/api/tutor/levels/all');
        levelOptions.value = levelsRes.data;
      } catch (error) {
        console.error('Failed to fetch form data:', error);
        // Fallback to hardcoded values if API fails
        subjectOptions.value = [
          { id: 1, name: 'Mathematics' },
          { id: 2, name: 'Science' },
          { id: 3, name: 'English' },
          { id: 4, name: 'Physics' },
          { id: 5, name: 'Chemistry' },
          { id: 6, name: 'Biology' }
        ];
        levelOptions.value = [
          { id: 1, name: 'Beginner' },
          { id: 2, name: 'Intermediate' },
          { id: 3, name: 'Advanced' }
        ];
      }
    };

    // Load user data
    const loadUserData = () => {
      const user = userStore.user;
      if (user) {
        // Set student_id from user->students->id relationship, otherwise null
        form.student_id = user.students?.id || user.student_id || null;
        
        // Pre-fill phone if exists
        if (user.phone) {
          const cleanPhone = user.phone.replace(/^\+91/, '').trim();
          phoneNumber.value = cleanPhone;
          form.phone = '+91' + cleanPhone;
        }
        // Pre-fill student name
        if (user.name) {
          form.student_name = user.name;
        }
      }
    };

    // Load existing requirement for edit mode
    const loadRequirement = async (id) => {
      try {
        const response = await axios.get(`/api/student/requirements/${id}`);
        const req = response.data.requirement;
        
        // Populate form with existing data
        form.student_id = req.student_id;
        form.city = req.city || '';
        form.area = req.area || '';
        form.pincode = req.pincode || '';
        form.phone = req.phone || '';
        form.alternate_phone = req.alternate_phone || '';
        form.student_name = req.student_name || '';
        form.description = req.details || '';
        form.level = req.level || '';
        form.service_type = req.service_type || '';
        form.meeting_options = Array.isArray(req.meeting_options) ? req.meeting_options : [];
        form.travel_distance = req.travel_distance || '';
        form.budget_amount = req.budget || '';
        form.budget_type = req.budget_type || '';
        form.gender_preference = req.gender_preference || '';
        form.availability = req.availability || '';
        form.languages = Array.isArray(req.languages) ? req.languages : [];
        form.tutor_location = req.tutor_location_preference || '';
        form.other_subject = req.other_subject || '';
        
        // Load subjects from relationship
        if (req.subjects && Array.isArray(req.subjects)) {
          form.subjects = req.subjects.map(s => s.name);
        }
        
        // Format phone numbers
        if (req.phone) {
          phoneNumber.value = req.phone.replace(/^\+91/, '').trim();
        }
        if (req.alternate_phone) {
          alternatePhoneNumber.value = req.alternate_phone.replace(/^\+91/, '').trim();
        }
      } catch (error) {
        console.error('Failed to load requirement:', error);
        errorMessage.value = 'Failed to load requirement data';
      }
    };

    const goBack = () => {
      router.push('/student/requirements');
    };

    // Phone number formatting
    const formatPhoneNumber = (event) => {
      let value = event.target.value.replace(/\D/g, '');
      if (value.length > 10) {
        value = value.slice(0, 10);
      }
      phoneNumber.value = value;
      form.phone = value ? '+91' + value : '';
    };

    const formatAlternatePhone = (event) => {
      let value = event.target.value.replace(/\D/g, '');
      if (value.length > 10) {
        value = value.slice(0, 10);
      }
      alternatePhoneNumber.value = value;
      form.alternate_phone = value ? '+91' + value : '';
    };

    // Subject handlers
    const toggleSubject = (subject) => {
      const index = form.subjects.indexOf(subject);
      if (index > -1) {
        form.subjects.splice(index, 1);
      } else {
        form.subjects.push(subject);
      }
    };

    const removeSubject = (subject) => {
      const index = form.subjects.indexOf(subject);
      if (index > -1) {
        form.subjects.splice(index, 1);
      }
    };

    const addCustomSubject = async () => {
      if (!customSubject.value) return;
      
      const subjectName = customSubject.value.trim();
      
      // Check if already selected
      if (form.subjects.includes(subjectName)) {
        customSubject.value = '';
        return;
      }
      
      try {
        // Add to database
        const response = await axios.post('/api/subjects', {
          name: subjectName
        });
        
        // Add to local options if successful
        if (response.data) {
          subjectOptions.value.push(response.data);
        }
        
        // Add to selected subjects
        form.subjects.push(subjectName);
        customSubject.value = '';
        
        // Show success feedback
        console.log('Subject added successfully');
      } catch (error) {
        console.error('Failed to add subject to database:', error);
        // Still add locally even if DB fails
        form.subjects.push(subjectName);
        customSubject.value = '';
      }
    };

    // Language handlers
    const toggleLanguage = (lang) => {
      const index = form.languages.indexOf(lang);
      if (index > -1) {
        form.languages.splice(index, 1);
      } else {
        form.languages.push(lang);
      }
    };

    const removeLanguage = (lang) => {
      const index = form.languages.indexOf(lang);
      if (index > -1) {
        form.languages.splice(index, 1);
      }
    };

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      const target = event.target;
      if (!target.closest('.relative')) {
        showSubjectDropdown.value = false;
        showLanguageDropdown.value = false;
      }
    };

    onMounted(() => {
      fetchFormData();
      
      // Check if we're in edit mode
      if (route.params.id) {
        isEditMode.value = true;
        requirementId.value = route.params.id;
        loadRequirement(route.params.id);
      } else {
        loadUserData();
      }
      
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    const validateStep = () => {
      errorMessage.value = '';
      // Make all fields optional - no validation required
      return true;
    };

    const submitRequest = async () => {
      if (!validateStep()) return;

      submitting.value = true;
      errorMessage.value = '';
      
      try {
        let response;
        if (isEditMode.value) {
          // Update existing requirement
          response = await axios.put(`/api/student/requirements/${requirementId.value}`, form);
        } else {
          // Create new requirement
          response = await axios.post('/api/student/request-tutor', form);
        }
        
        showSuccess.value = true;
        
        // Scroll to top to show success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Redirect after 2 seconds
        setTimeout(() => {
          router.push('/student/requirements');
        }, 2000);
      } catch (error) {
        errorMessage.value = error.response?.data?.message || `Error ${isEditMode.value ? 'updating' : 'submitting'} request. Please try again.`;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } finally {
        submitting.value = false;
      }
    };

    return {
      currentSection,
      sections,
      isSectionCompleted,
      completionPercentage,
      completedSectionsCount,
      allRequiredFieldsFilled,
      form,
      submitting,
      errorMessage,
      showSuccess,
      isEditMode,
      phoneNumber,
      alternatePhoneNumber,
      subjectOptions,
      subjectSearch,
      showSubjectDropdown,
      customSubject,
      filteredSubjects,
      levelOptions,
      genderOptions,
      languageOptions,
      languageSearch,
      showLanguageDropdown,
      filteredLanguages,
      goBack,
      formatPhoneNumber,
      formatAlternatePhone,
      toggleSubject,
      removeSubject,
      addCustomSubject,
      toggleLanguage,
      removeLanguage,
      submitRequest
    };
  }
};
</script>
