<?php
echo "<h1>PHP is Working! 🎉</h1>";
echo "<p>MySQL Status: ";
echo (extension_loaded('mysqli') ? "✅ Enabled" : "❌ Disabled");
phpinfo(); // Shows PHP configuration details