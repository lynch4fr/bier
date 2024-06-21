# bier
Plugins LibreNMS: BIER
-----------------------------------------
BIER: Brew Infrastructure and Equipment netwoRk
-----------------------------------------
BIER offers a view of your switchs as you were standing in front of your equipment in the rack, i.e. per 50 ports switch or 24 ports on 2 rows.
View screenshot 

Tested on ERS extreme 45xx and 36xx range switches.

To install the BIER plug-in on LibreNMS
- Copy bier.inc.php in /../includes/html/pages/device/
- Copy BierController.php in /../app/Http/Controllers/Device/Tabs/
- Edit file /../app/Http/Controllers/DeviceController.php
- Add line 32
-      ...
       'bier' => \App\Http\Controllers\Device\Tabs\BierController::class
       ...
- Execute ./validate and ./daily
