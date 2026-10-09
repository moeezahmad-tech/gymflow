<?php
/**
 * GymFlow - Global Reusable Footer Component
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}
?>
<!-- Global Footer -->
<footer class="bg-[#050507] border-t border-zinc-900 text-zinc-400 relative overflow-hidden mt-auto">
    <!-- Subtle Top Glow Gradient -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3/4 h-[1px] bg-gradient-to-r from-transparent via-red-600/50 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
            
            <!-- Column 1: Brand & Bio -->
            <div class="lg:col-span-2 space-y-5">
                <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
                    <img src="<?= asset('images/logo.png') ?>" 
                         alt="Gym Flow" 
                         class="h-9 w-auto object-contain brightness-100 invert-0"
                         onerror="this.style.display='none';" />
                    <span class="font-heading text-3xl font-bold tracking-wider text-white">
                        GYM<span class="text-red-500">FLOW</span>
                    </span>
                </a>
                <p class="text-sm text-zinc-400 leading-relaxed max-w-md">
                    GymFlow is a high-performance fitness club & management ecosystem engineered to empower athletes, bodybuilders, and fitness enthusiasts through elite coaching and modern training tools.
                </p>

                <!-- Social Links -->
                <div class="flex items-center space-x-3 pt-2">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300 hover:text-white hover:bg-red-600 hover:border-red-600 transition-all duration-200" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f text-sm"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300 hover:text-white hover:bg-red-600 hover:border-red-600 transition-all duration-200" aria-label="Instagram">
                        <i class="fa-brands fa-instagram text-sm"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300 hover:text-white hover:bg-red-600 hover:border-red-600 transition-all duration-200" aria-label="YouTube">
                        <i class="fa-brands fa-youtube text-sm"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300 hover:text-white hover:bg-red-600 hover:border-red-600 transition-all duration-200" aria-label="X/Twitter">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Column 2: Navigation Links -->
            <div class="space-y-4">
                <h4 class="font-heading text-lg font-bold text-white tracking-wider uppercase border-b border-zinc-800 pb-2">Quick Links</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="<?= url('index.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> Home</a></li>
                    <li><a href="<?= url('about.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> About Our Gym</a></li>
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> Training Programs</a></li>
                    <li><a href="<?= url('facilities.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> Facilities & Gear</a></li>
                    <li><a href="<?= url('pricing.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> Membership Plans</a></li>
                    <li><a href="<?= url('contact.php') ?>" class="hover:text-red-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs text-red-500"></i> Contact & Hours</a></li>
                    <li><button type="button" onclick="window.triggerPWAInstall()" class="hover:text-red-400 transition-colors flex items-center gap-2 text-left"><i class="fa-solid fa-mobile-screen text-xs text-red-500"></i> Install Mobile App</button></li>
                </ul>
            </div>

            <!-- Column 3: Training Programs -->
            <div class="space-y-4">
                <h4 class="font-heading text-lg font-bold text-white tracking-wider uppercase border-b border-zinc-800 pb-2">Programs</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors">Cardio Shred & HIIT</a></li>
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors">Power Barbell Lifting</a></li>
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors">Combat Boxing & Striking</a></li>
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors">Hypertrophy Lab</a></li>
                    <li><a href="<?= url('programs.php') ?>" class="hover:text-red-400 transition-colors">Thermal Recovery Sauna</a></li>
                </ul>
            </div>

            <!-- Column 4: Newsletter & Working Hours -->
            <div class="space-y-4">
                <h4 class="font-heading text-lg font-bold text-white tracking-wider uppercase border-b border-zinc-800 pb-2">Newsletter</h4>
                <p class="text-xs text-zinc-400">Subscribe for workout tips, fitness guides, and membership promotions.</p>
                
                <form id="newsletterForm" action="<?= url('api/subscribe.php') ?>" method="POST" class="space-y-2" onsubmit="handleNewsletterSubmit(event, this)">
                    <div class="relative">
                        <input type="email" id="newsletterEmail" name="email" required placeholder="Enter your email" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-3 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                    <button type="submit" id="newsletterSubmitBtn" class="w-full btn-primary text-xs uppercase font-bold tracking-wider py-2.5 rounded-lg flex items-center justify-center gap-2 cursor-pointer transition-all">
                        <span id="newsletterBtnText">Subscribe</span>
                        <i id="newsletterBtnIcon" class="fa-regular fa-paper-plane text-[11px]"></i>
                    </button>
                    <p id="newsletterMsg" class="text-[11px] hidden pt-1"></p>
                </form>

                <script>
                async function handleNewsletterSubmit(e, form) {
                    e.preventDefault();
                    const input = form.querySelector('#newsletterEmail');
                    const btn = form.querySelector('#newsletterSubmitBtn');
                    const btnText = form.querySelector('#newsletterBtnText');
                    const btnIcon = form.querySelector('#newsletterBtnIcon');
                    const msgEl = form.querySelector('#newsletterMsg');
                    const email = input.value.trim();

                    if (!email) return;

                    btn.disabled = true;
                    btn.classList.add('opacity-75');
                    btnText.textContent = 'Subscribing...';
                    btnIcon.className = 'fa-solid fa-circle-notch fa-spin text-[11px]';
                    if (msgEl) msgEl.className = 'hidden';

                    try {
                        const res = await fetch('<?= url("api/subscribe.php") ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ email: email })
                        });
                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            if (typeof showToast === 'function') {
                                showToast(data.message, 'success', 'Subscribed!');
                            }
                            if (msgEl) {
                                msgEl.className = 'text-[11px] text-emerald-400 block font-medium';
                                msgEl.textContent = '✓ ' + data.message;
                            }
                            form.reset();
                        } else {
                            const err = data.message || 'Could not subscribe. Please try again.';
                            if (typeof showToast === 'function') {
                                showToast(err, 'error', 'Subscription Failed');
                            }
                            if (msgEl) {
                                msgEl.className = 'text-[11px] text-red-400 block font-medium';
                                msgEl.textContent = '✗ ' + err;
                            }
                        }
                    } catch (err) {
                        if (typeof showToast === 'function') {
                            showToast('Network error. Please try again later.', 'error', 'Error');
                        }
                        if (msgEl) {
                            msgEl.className = 'text-[11px] text-red-400 block font-medium';
                            msgEl.textContent = '✗ Network error. Please try again.';
                        }
                    } finally {
                        btn.disabled = false;
                        btn.classList.remove('opacity-75');
                        btnText.textContent = 'Subscribe';
                        btnIcon.className = 'fa-regular fa-paper-plane text-[11px]';
                    }
                }
                </script>
            </div>

        </div>

        <!-- Bottom Copyright & Legal Links -->
        <div class="mt-14 pt-8 border-t border-zinc-900 flex flex-col sm:flex-row items-center justify-between text-xs text-zinc-500 gap-4">
            <p>&copy; <?= date('Y') ?> <span class="text-zinc-300 font-semibold">GymFlow</span> Inc. All rights reserved.</p>
            
            <div class="flex items-center space-x-6">
                <a href="<?= url('privacy.php') ?>" class="hover:text-zinc-300 transition-colors">Privacy Policy</a>
                <a href="<?= url('terms.php') ?>" class="hover:text-zinc-300 transition-colors">Terms of Service</a>
                <a href="<?= url('login.php') ?>" class="text-zinc-500 hover:text-zinc-300 transition-colors">Member Login</a>
            </div>
        </div>
    </div>
</footer>

<!-- Global JavaScript Bundle -->
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
