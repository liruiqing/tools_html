@echo off
for /f  %%i in (name.txt) do if not exist %%i (echo %%i 文件不存在。)
pause>nul
