prerequisits: please see www.2aida.org for donwloading a glucose simulation csv file into a file renamed to "input.2aida"

This part of the project (aida_interpol0) does the following:


1- stores an aida file output "input.2aida" into a mysql database
2- interpolates the 24 hour output values simulated from 15 min intervals into 1 min intervals, phase2_interpol_aida_run24.php file calls csv_interpol_aida0


step 1 is implemented in the file "phase1_read_aida_csv.php"
step 2 is implemented in the file "phase2_interpol_aida_run24.php"


