<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">

    @vite('resources/css/volunteers.css')

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

header {
    position: sticky;
    top: 0;
    z-index: 999;
    background-color: #003DA5;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
}

        header .logo {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        header nav a {
            color: white;
            margin: 0 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        header nav a:hover {
            opacity: 0.8;
        }

        .login-bg-wrapper {
            background: url('/images/login_bg.png') no-repeat center center;
            background-size: cover;
            min-height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
        }

.login-container {
    background-color: white;
    width: 100%;
    max-width: 500px; /* adjust as needed */
    margin: 2rem; /* remove auto-centering limit */
    padding: 2.3rem;
    border: 2px solid #003DA5;
    border-radius: 25px;
    text-align: center;
    color: #003DA5;
}


        .login-container h2 {
            color: #003DA5;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
        }

        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .login-container input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #003DA5;
            border-radius: 5px;
        }

        .login-container button {
            background-color: #003DA5;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
        }

section {
    display: block;
}

#login-container {
    display: none;
}

#home-container {
    display: block;
}

html {
    scroll-behavior: smooth;
}

.feature-card {
    background:white;
    padding:1.4rem 1.4rem;   /* reduced height */
    border-radius:25px;
    width:360px;
    color:#003DA5;
    box-shadow: 0px 8px 18px rgba(0,0,0,0.25);
    transition: 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-card h3 {
    font-size:1.45rem;       /* slightly smaller */
    font-weight:800;
    margin-top:0.6rem;       /* reduced spacing */
    margin-bottom:0.5rem;
}

.feature-card p {
    font-size:1rem;          /* smaller text */
    line-height:1.3rem;
    margin-bottom:0;         /* no extra space */
}

/* ICON STYLE */
.feature-icon {
    width:48px;              /* slightly smaller */
    height:auto;
    margin-bottom:0.5rem;    /* tighter spacing */
}

.section-wrapper-fullscreen {
    background: white;
    min-height: calc(100vh - 70px);
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 3rem 2rem;
}


.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;
    width: 100%;
    max-width: 1300px;
    gap: 0;
}

.about-box {
    padding: 2.5rem;
    font-size: 1.1rem;
    line-height: 1.6rem;
}

.about-title {
    font-size: 2.3rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

/* LEFT — About Us (White) */
.about-left {
    background: white;
    grid-column: 1;
    grid-row: 1 / span 2; /* LEFT SIDE becomes tall column */
}

.about-mission {
    background: #FFD500;
    color: #003DA5;
    grid-column: 2;
    grid-row: 1;
}

.about-vision {
    background: #003DA5;
    color: white;
    grid-column: 2;
    grid-row: 2;
}



    </style>
</head>

<body>

<header>
    <div class="logo">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
        NU Fairview - Student Development and Activities Office
    </div>

    <nav>
        <a href="#home-container">HOME</a>
        <a href="#about-container">ABOUT US</a>
        <a href="#contact-container">CONTACT US</a>

        <a id="login-btn"
           style="background:#FFC300; color:#003DA5; padding:8px 18px; border-radius:20px; font-weight:700; text-decoration:none; margin-left:1rem; cursor:pointer;">
           LOG IN
        </a>

    </nav>
</header>


<!-- ========================================= -->
<!-- ✅ HOME SECTION FROM YOUR IMAGE           -->
<!-- ========================================= -->

<section id="home-container" style="width:100%; background:white;">

    <img src="/images/landing_bg.jpg" 
         alt="NU Building" 
         style="width:100%; height:auto;">

    <div style="text-align:center; padding:3rem 1rem;">
        <h1 style="color:#003DA5; font-size:3rem; font-weight:800;">IsKonnect</h1>
        <p style="font-size:1.2rem; max-width:900px; margin:auto;">
            Empowering scholars and campus offices with a smarter way to manage volunteerism, 
            track progress, and stay connected—all through one platform.
        </p>
    </div>

    <div style="background:#003DA5; color:white; padding:3rem 1rem; text-align:center;">
        <h2 style="font-size:2.5rem; font-weight:800;">Here to Improve</h2>

        <!-- 3 Boxes -->
<!-- FEATURES SECTION (Matches Your Image) -->
<div style="display:flex; justify-content:center; gap:2.5rem; margin-top:2rem; flex-wrap:wrap;">

    <!-- CARD 1 -->
    <div class="feature-card">
        <img src="/images/icons/scholar.png" alt="Scholar Icon" class="feature-icon">
        <h3>Scholar Management</h3>
        <p>Efficiently track and manage scholars and their academic progress all in one place.</p>
    </div>

    <!-- CARD 2 -->
    <div class="feature-card">
        <img src="/images/icons/volunteer.png" alt="Volunteer Icon" class="feature-icon">
        <h3>Volunteer Coordination</h3>
        <p>Organize volunteer activities, track participation, and simplify communication.</p>
    </div>

    <!-- CARD 3 -->
    <div class="feature-card">
        <img src="/images/icons/analytics.png" alt="Analytics Icon" class="feature-icon">
        <h3>Analytics & Reports</h3>
        <p>Generate comprehensive reports and gain insights into performance and engagement.</p>
    </div>

</div>

    </div>

</section>



<!-- ========================================= -->
<!-- ✅ ABOUT SECTION FROM IMAGE               -->
<!-- ========================================= -->

<section id="about-container" style="display:flex; justify-content:center; padding:3rem 1rem; background:#f4f4f4;">

    <div class="about-grid">

        <!-- LEFT: About Us -->
        <div class="about-box about-left">
            <h2 class="about-title" style="color:#003DA5;">About Us</h2>

            <p>IsKonnect is a web-based scholar and volunteer management system developed to support 
               NU Fairview’s Student Development and Activities Office (SDAO).</p>

            <p style="margin-top:1rem;">
               Designed to replace manual processes from scholar record management and volunteer hour tracking 
               to campus office requests and reporting.
            </p>

            <p style="margin-top:1rem;">
               Our goal is to strengthen volunteer engagement, simplify administrative tasks, 
               and support the overall growth and development of the NU scholar community.
            </p>
        </div>

        <!-- TOP RIGHT: Mission -->
        <div class="about-box about-mission">
            <h2 class="about-title">Mission</h2>

            <p>
                To provide NU Fairview with an efficient, secure, and transparent digital platform that 
                simplifies scholar management, enhances volunteer coordination, and supports administrators 
                with accurate, real-time data, reducing manual workload and improving the student service 
                experience.
            </p>
        </div>

        <!-- BOTTOM RIGHT: Vision -->
        <div class="about-box about-vision">
            <h2 class="about-title">Vision</h2>

            <p>
                To become NU Fairview’s trusted digital hub for scholar and volunteer engagement—
                a fully automated and integrated system that empowers students, simplifies office operations, 
                and fosters a culture of service, accountability, and continuous development.
            </p>
        </div>

    </div>

</section>




<!-- ========================================= -->
<!-- ✅ CONTACT SECTION FROM IMAGE             -->
<!-- ========================================= -->
<section id="contact-container" class="section-wrapper-fullscreen"
    style="background:#003DA5; color:white; flex-wrap:wrap; gap:2rem; align-items:flex-start; justify-content:center; padding:3rem 2rem;">

    <div style="flex:1; min-width:300px; max-width:450px;">
        <h2 style="font-size:2.3rem; font-weight:800;">Contact Us</h2>

        <div style="margin-top:1.5rem; display:flex; flex-direction:column; gap:1.2rem;">

            <!-- Facebook -->
            <div style="display:flex; align-items:flex-start; gap:0.8rem;">
                <img src="/images/icons/facebook.png"
                     alt="Facebook Icon"
                     style="width:28px; height:28px; flex-shrink:0;">
                <div>
                    <strong>Facebook</strong><br>
                    https://web.facebook.com/NUFairviewSDA
                </div>
            </div>

            <!-- Outlook Email -->
            <div style="display:flex; align-items:flex-start; gap:0.8rem;">
                <img src="/images/icons/email.png"
                     alt="Email Icon"
                     style="width:28px; height:28px; flex-shrink:0;">
                <div>
                    <strong>Outlook</strong><br>
                    nufv-sdao@nu-fairview.edu.ph
                </div>
            </div>

            <!-- Phone -->
            <div style="display:flex; align-items:flex-start; gap:0.8rem;">
                <img src="/images/icons/phone.png"
                     alt="Phone Icon"
                     style="width:28px; height:28px; flex-shrink:0;">
                <div>
                    <strong>Phone Number</strong><br>
                    0917 - 821 - 4622
                </div>
            </div>

        </div>
    </div>


    <!-- FORM -->
    <div style="
        flex:1.4; 
        min-width:360px; 
        max-width:650px; 
        background:white; 
        color:#003DA5; 
        padding:2.3rem; 
        border-radius:20px;
    ">
<form method="POST" action="/admin-login">
    @csrf

    <div style="display:flex; gap:1rem;">
        <div style="flex:1;">
            <label>First Name</label>
            <input type="text" name="first_name" required placeholder="Enter First Name"
                   style="width:100%; padding:0.75rem; border:1px solid #003DA5; border-radius:5px; margin-bottom:1rem;">
        </div>

        <div style="flex:1;">
            <label>Last Name</label>
            <input type="text" name="last_name" required placeholder="Enter Last Name"
                   style="width:100%; padding:0.75rem; border:1px solid #003DA5; border-radius:5px; margin-bottom:1rem;">
        </div>
    </div>

    <label>School Email</label>
    <input type="email" name="email" required placeholder="Enter Email"
           style="width:100%; padding:0.75rem; border:1px solid #003DA5; border-radius:5px; margin-bottom:1rem;">


           <label>Subject</label>
<input type="text" name="subject" required placeholder="Enter Subject"
       style="width:100%; padding:0.75rem; border:1px solid #003DA5; border-radius:5px; margin-bottom:1rem;">


       
    <label>Message</label>
    <textarea name="message" required
              style="width:100%; padding:0.75rem; border:1px solid #003DA5; border-radius:5px; height:120px; margin-bottom:1rem;"></textarea>

    <button type="submit"
        style="background:#003DA5; color:white; padding:0.75rem 2rem; border-radius:25px; border:none; margin-top:0.5rem; cursor:pointer;">
        Submit
    </button>
</form>


    </div>
</section>



    </div>
</section>




<!-- ========================================= -->
<!-- LOGIN FORM (UNCHANGED)                    -->
<!-- ========================================= -->

<div id="login-container" class="login-bg-wrapper">
    <div class="login-container">
        <h2>Log In</h2>

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            @if ($errors->any())
                <div class="mb-2 text-sm text-red-500">
                    {{ $errors->first() }}
                </div>
            @endif

<p>
    <a href="{{ route('password.request') }}" style="font-style: normal; color:#6c757d; text-decoration:underline; cursor:pointer;">
        Forgot your password?
    </a>
</p>

            <button type="submit">Log In</button>
        </form>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const loginBtn = document.getElementById("login-btn");
    const loginContainer = document.getElementById("login-container");
    const sections = document.querySelectorAll("section");

    // ⬅️ Save each section's ORIGINAL display so we can restore it correctly
    sections.forEach(sec => {
        sec.dataset.origDisplay = window.getComputedStyle(sec).display;
    });

    // When LOG IN is clicked
    loginBtn.addEventListener("click", () => {
        sections.forEach(sec => sec.style.display = "none");
        loginContainer.style.display = "flex";
        window.scrollTo({ top: 0 });
    });

    // When HOME / ABOUT / CONTACT are clicked
    document.querySelectorAll("header nav a[href^='#']").forEach(link => {
        link.addEventListener("click", (e) => {
            const target = document.querySelector(link.getAttribute("href"));
            if (!target) return;

            e.preventDefault();

            // Restore each section's correct original display (flex, block, etc.)
            sections.forEach(sec => {
                sec.style.display = sec.dataset.origDisplay;
            });

            // Hide login form
            loginContainer.style.display = "none";

            // Smooth scroll
            target.scrollIntoView({ behavior: "smooth" });
        });
    });
});
</script>

</body>
</html>
