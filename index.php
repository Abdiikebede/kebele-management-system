<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ifa Bula Kebele</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
  </style>
</head>
<body class="bg-gradient-to-b from-blue-50 to-white text-gray-900 min-h-screen flex flex-col font-sans">

  <!-- Navigation Bar -->
  <nav class="sticky top-0 z-50 w-full flex justify-between items-center py-5 px-10 bg-white/80 backdrop-blur-md shadow-md">
    <div class="flex items-center space-x-3">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Example_logo.png" alt="Logo" class="w-10 h-10 rounded-full">
      <span class="font-extrabold text-2xl text-blue-700">Ifa Bula Kebele</span>
    </div>
    <ul class="hidden md:flex space-x-8 text-lg font-medium">
      <li><a href="#home" class="hover:text-blue-600 transition">Home</a></li>
      <li><a href="#service" class="hover:text-blue-600 transition">Service</a></li>
      <li><a href="#about" class="hover:text-blue-600 transition">About</a></li>
     
      <li><a href="#contact" class="hover:text-blue-600 transition">Contact</a></li>
    </ul>
    <button class="md:hidden text-gray-800 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </nav>

  <!-- Main Section -->
  <section id="home" class="flex flex-col md:flex-row items-center justify-center gap-10 p-10 flex-grow">
    <!-- Left Image -->
    <div class="w-72 h-72 overflow-hidden rounded-full shadow-2xl border-4 border-blue-300">
      <img src="./jimma.jpg" alt="Community" class="w-full h-full object-cover" />
    </div>

    <!-- Text Section -->
    <div class="text-center md:text-left max-w-md">
      <h1 class="text-5xl font-extrabold leading-tight mb-6 text-blue-700">Welcome to <br> Ifa Bula Kebele</h1>
      <p class="text-lg text-gray-600 mb-6">Your local government office dedicated to serving the community with transparency and care.</p>
      <div class="space-x-4">
       <a href="./auth/login.php"> <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg transition transform hover:scale-105">
          Log In
        </button>
        </a>
        <a href="./auth/registration.php"><button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg transition transform hover:scale-105">
          Register
        </button>
        </a>
      </div>
    </div>

    <!-- Right Image -->
    <div class="w-72 h-72 overflow-hidden rounded-full shadow-2xl border-4 border-blue-300">
      <img src="./jimma2.jpg" alt="Office" class="w-full h-full object-cover" />
    </div>
  </section>

  <!-- ==================== SERVICE SECTION ==================== -->
  <section id="service" class="py-16 px-6 bg-white">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="text-4xl font-extrabold text-blue-700 mb-4">Our Services</h2>
      <p class="text-lg text-gray-600 mb-12">We provide essential services to support our community.</p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Service 1 -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-2">
          <div class="w-16 h-16 mx-auto mb-5 bg-blue-600 text-white rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-3">Document Issuance</h3>
          <p class="text-gray-600 mb-5">Birth certificates, ID cards, residence proofs – fast and reliable.</p>
          <a href="#" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-full transition">Learn More</a>
        </div>

        <!-- Service 2 -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-2">
          <div class="w-16 h-16 mx-auto mb-5 bg-green-600 text-white rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-3">Appointment Booking</h3>
          <p class="text-gray-600 mb-5">Book your visit online and save time at the office.</p>
          <a href="#" class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-5 rounded-full transition">Learn More</a>
        </div>

        <!-- Service 3 -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-2">
          <div class="w-16 h-16 mx-auto mb-5 bg-indigo-600 text-white rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-3">Community Support</h3>
          <p class="text-gray-600 mb-5">File complaints, request aid, or join local programs.</p>
          <a href="#" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-full transition">Learn More</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== ABOUT US SECTION ==================== -->
  <section id="about" class="py-16 px-6 bg-gradient-to-b from-white to-blue-50">
    <div class="max-w-6xl mx-auto">
      <div class="text-center mb-12">
        <h2 class="text-4xl font-extrabold text-blue-700 mb-4">About Ifa Bula Kebele</h2>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">Serving our community with integrity, efficiency, and care since establishment.</p>
      </div>

      <div class="grid md:grid-cols-2 gap-10 items-center">
        <!-- Text Content -->
        <div>
          <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
          <p class="text-gray-600 mb-6">
            To deliver transparent, accessible, and timely public services that improve the quality of life for every resident in Ifa Bula Kebele.
          </p>
          <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
          <p class="text-gray-600 mb-6">
            A modern, digital-first kebele office where every citizen feels heard, supported, and empowered.
          </p>
          <div class="flex space-x-4">
            <div class="text-center">
              <div class="text-4xl font-extrabold text-blue-600">5000+</div>
              <p class="text-gray-600">Residents Served</p>
            </div>
            <div class="text-center">
              <div class="text-4xl font-extrabold text-green-600">98%</div>
              <p class="text-gray-600">Satisfaction Rate</p>
            </div>
            <div class="text-center">
              <div class="text-4xl font-extrabold text-indigo-600">24/7</div>
              <p class="text-gray-600">Online Access</p>
            </div>
          </div>
        </div>

        <!-- Image -->
        <div class="rounded-2xl overflow-hidden shadow-2xl border-4 border-blue-200">
          <img src="https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?auto=format&fit=crop&w=800&q=80" alt="Kebele Office" class="w-full h-full object-cover">
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== CONTACT US SECTION ==================== -->
  <section id="contact" class="py-16 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
      <h2 class="text-4xl font-extrabold text-blue-700 text-center mb-12">Get in Touch</h2>

      <div class="grid md:grid-cols-2 gap-10">
        <!-- Contact Form -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl shadow-xl">
          <h3 class="text-2xl font-bold text-gray-800 mb-6">Send Us a Message</h3>
          <form action="#" class="space-y-5">
            <div>
              <label class="block text-gray-700 font-medium mb-2">Full Name</label>
              <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="John Doe" required>
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">Email</label>
              <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="you@example.com" required>
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">Subject</label>
              <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Inquiry about service">
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">Message</label>
              <textarea rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-full shadow-lg transition transform hover:scale-105">
              Send Message
            </button>
          </form>
        </div>

        <!-- Contact Info + Map -->
        <div>
          <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl shadow-xl mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Contact Information</h3>
            <div class="space-y-4 text-gray-700">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <p>Ifa Bula Kebele, Zone 3, Woreda 12, Ethiopia</p>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <p>+251 911 223 344</p>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <p>info@ifabulakebele.gov.et</p>
              </div>
            </div>
          </div>

          <!-- Google Map -->
          <div class="rounded-2xl overflow-hidden shadow-xl border-4 border-blue-200 h-64">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3914.123456789!2d39.260000!3d8.550000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b8d7c00000000%3A0xabcdef123456789!2sIfa%20Bula%20Kebele!5e0!3m2!1sen!2set!4v1697666666666!5m2!1sen!2set"
              width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-blue-700 text-white text-center py-6 mt-10">
    <p class="text-sm">&copy; 2025 Ifa Bula Kebele. All rights reserved. | Designed with care for the community.</p>
  </footer>

</body>
</html>
