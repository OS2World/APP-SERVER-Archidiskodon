#include "../4os2/header.h"

VOID main( INT argc, PPCHAR argv )
{
 if( argc != 2 ) DosExit( EXIT_PROCESS, ERROR_INVALID_HANDLE );
 if( !atoi( argv[ 1 ] ) ) DosExit( EXIT_PROCESS, ERROR_INVALID_HANDLE );

 if( DosVerifyPidTid( atoi( argv[ 1 ] ), 1 ) == NO_ERROR )
  {
   Say( argv[ 1 ] );
  }
 else
  {
   Say( "0" );
  }

 DosExit( EXIT_PROCESS, NO_ERROR );
}
