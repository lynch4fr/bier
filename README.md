# bier
BIER: Brew Infrastructure and Equipment netwoRk
-----------------------------------------
Plugins LibreNMS: BIER
-----------------------------------------
BIER offers a view of your switchs as you were standing in front of your equipment in the rack, i.e. per 50-port switch or 24 ports on 2 rows.
View screenshot 

Tested on ERS extreme 45xx and 36xx range switches.

To install the BIER plug-in on LibreNMS
- Copy print-interface-bier.inc.php in /.../librenms/html/plugins/BIER/
- Create BIER link for equipment ports tab (still to be coded)
- Edit file /.../librenms/includes/html/pages/device/ports.inc.php
- Line 98 to 108 insert below + elseif {... instead of "else"
-     ...
      $type_sep = ' | ';
      }//end foreach
  
      //DEBUT AJOUT DE CODE
                    echo ' | ';
                    echo generate_link('BIER', $link_array, ['view' => 'bier']);

                print_optionbar_end();
                // Si on clique sur Bier
                if ($vars['view'] == "bier") {
                    include 'html/plugins/BIER/print-interface-bier.inc.php';
                }
       //FIN AJOUT DE CODE
  
      elseif ($vars['view'] == 'minigraphs') {...
