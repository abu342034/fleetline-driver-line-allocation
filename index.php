<?php $pageTitle = 'Home'; include 'includes/header.php'; ?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-fl">
  <div class="container">
    <a class="navbar-brand brand-logo" href="#"><i class="fas fa-route me-2"></i>FleetLine</a>
    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#how">How it Works</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
        <li class="nav-item"><a class="nav-link" href="driver/register.php"><i class="fas fa-user-plus me-1"></i>Driver Registration</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home" style="padding-top:70px;padding-bottom:70px">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1>Smart Driver & Line Allocation Management</h1>
        <p>FleetLine streamlines how transport operators assign drivers and vehicles to routes — eliminating double-bookings, boosting accountability, and keeping every line running on schedule.</p>
        <div class="mt-4">
          <a href="login.php" class="btn-navy me-2"><i class="fas fa-arrow-right-to-bracket me-2"></i>Get Started</a>
          <a href="driver/register.php" class="btn-outline-navy">Driver Registration</a>
        </div>
      </div>
      <div class="col-lg-5 text-center d-none d-lg-block">
        <i class="fas fa-bus-simple" style="font-size:14rem;opacity:.18"></i>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" style="background:#fff">
  <div class="container">
    <div class="section-title">
      <h2>How It Works</h2>
      <p>Three simple steps to take full control of your fleet operations.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card">
          <div class="circle-icon"><i class="fas fa-user-plus"></i></div>
          <h4>Register Resources</h4>
          <p>Add your drivers, vehicles, and routes into a single, organized dashboard.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="circle-icon"><i class="fas fa-shuffle"></i></div>
          <h4>Smart Allocation</h4>
          <p>Assign drivers and vehicles to routes — the system blocks any double-booking automatically.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="circle-icon"><i class="fas fa-clipboard-check"></i></div>
          <h4>Track Schedules</h4>
          <p>Drivers log in to view only their assigned routes, dates, and times in real-time.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact" style="background:var(--light)">
  <div class="container">
    <div class="section-title">
      <h2>Contact Us</h2>
      <p>Got questions or need a demo? Reach out to the FleetLine team — we're happy to help.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="contact-card">
          <div class="circle-icon"><i class="fas fa-location-dot"></i></div>
          <h4>Our Location</h4>
          <p>ADUSTECH, Faculty of Computing,<br>Wudil, Kano State, Nigeria.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card">
          <div class="circle-icon"><i class="fas fa-phone"></i></div>
          <h4>Call Us</h4>
          <p><a href="tel:07047327262" style="color:inherit;text-decoration:none">07047327262</a><br>Mon &ndash; Fri, 9:00am &ndash; 5:00pm</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card">
          <div class="circle-icon"><i class="fas fa-envelope"></i></div>
          <h4>Email Us</h4>
          <p><a href="mailto:info@binhafiz.dev" style="color:inherit;text-decoration:none">info@binhafiz.dev</a><br>We reply within 24 hours.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
