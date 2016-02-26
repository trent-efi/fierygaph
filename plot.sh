
set grid
set title 'BP and Heartrate'
set yrange [50:160]
set xlabel 'time (military)'
set label 'finished walk' at 15, 140
unset label
set label 'finished walk' at 15, 105
plot 'num.csv' u 1:2 w lp t 'systolic', 'num.csv' u 1:3 w lp t 'diastolic', 'num.csv' u 1:4 w lp t 'heartrate'
