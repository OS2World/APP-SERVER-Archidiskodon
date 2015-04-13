@Echo off

Echo ÿ 
If /%1/ == // Icc.exe . > NUL
If /%1/ == // Exit

Icc.exe /Ss+ /G5 /Gf+ /Gi+ /Gs+ /O+ /Oi+ %1 %2 %3 %4 %5 %6 %7 %8 %9
Del /F *.obj

Exit
