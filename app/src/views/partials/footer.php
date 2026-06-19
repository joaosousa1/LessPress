</main>
<div class="container">
  <footer>
    <p><small>&copy; <a href="https://jspro.cc" target="_blank">João Sousa 2026</a> All rights reserved.<br>Built with Enterprise Security standards.</small></p>
  </footer>
</div>
</body>
<script>
  // Event 'window.load' wait for (images, scripts, css)
  window.addEventListener('load', () => {
    const loader = document.getElementById('loader-container');
    const content = document.getElementById('main-content');
    setTimeout(() => {
      // loader fade-out
      loader.classList.add('hidden');
      // show loader fade-in
      content.classList.add('visible');
    }, 300);
  });
</script>

</html>