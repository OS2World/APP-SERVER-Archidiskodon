#define INCL_DOS
#define INCL_DOSERRORS
#define INCL_DOSPROCESS

#include <OS2.h>
#include <String.h>
#include <StdLib.h>

#define STDIN   0
#define STDOUT  1
#define STDERR  2

VOID Say ( PBYTE String ) { ULONG Written = 0; DosWrite( STDOUT, String, strlen( String ), &Written ); }
VOID Err ( PBYTE String ) { ULONG Written = 0; DosWrite( STDERR, String, strlen( String ), &Written ); }

#define PPCHAR PCHAR*

APIRET APIENTRY DosVerifyPidTid ( ULONG Pid, ULONG Tid );
