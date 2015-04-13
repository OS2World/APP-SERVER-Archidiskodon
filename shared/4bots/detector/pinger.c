#include "../4os2/header.h"
#include "../4os2/errors.h"

/* * */

#define THREADS 256
#define MAXADDR 254

#define NUMBER_LENGTH  16
#define NETWORK_LENGTH 16
#define INTNAME_LENGTH 64
#define COMMAND_LENGTH 256
#define MESSAGE_LENGTH 256
#define REPORT_LENGTH  1024

TID Threads[ THREADS ];
CHAR Pipe_name[ THREADS ][ INTNAME_LENGTH ];
CHAR When_ready[ THREADS ][ INTNAME_LENGTH ];

#define EVTSEM_TIMEOUT (10*1000)
#define REPORT_TIMEOUT (30*1000)

VOID APIENTRY PingerThread ( ULONG Cell_number );

/* * */

VOID main( INT argc, PPCHAR argv )
{
 CHAR Network[ NETWORK_LENGTH ] = "";
 APIRET Error_code = NO_ERROR;

 if( argc != 2 ) DosExit( EXIT_PROCESS, -1 );
 if( !strstr( argv[ 1 ], "." ) ) DosExit( EXIT_PROCESS, -1 );

 {
  CHAR Semaphore_name[] = "/SEM32/PINGER-ALREADY-RUNNING";
  HMTX hmtxAlreadyRunning = NULLHANDLE;

  if( DosOpenMutexSem( Semaphore_name, &hmtxAlreadyRunning ) == NO_ERROR )
   {
    DosExit( EXIT_PROCESS, 1 );
   }
  else
   {
    Error_code = DosCreateMutexSem( Semaphore_name, &hmtxAlreadyRunning, DC_SEM_SHARED, 1 );
    if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );
   }
 }

 strcpy( Network, argv[ 1 ] ); argv[ 1 ][ 0 ] = 0;

 {
  INT Counter = 0; CHAR String[ NUMBER_LENGTH ] = "";
  for( Counter = 0; Counter < THREADS; Counter ++ )
   {
    Threads[ Counter ] = 0;

    strcpy( Pipe_name[ Counter ], "/PIPE/CHECK-IP/" );
    strcat( Pipe_name[ Counter ], Network );
    strcat( Pipe_name[ Counter ], "." );
    itoa( Counter, String, 10 ); strcat( Pipe_name[ Counter ], String );

    strcpy( When_ready[ Counter ], "/SEM32/PIPE-READY/" );
    strcat( When_ready[ Counter ], String );
   }
 }

 {
  INT Counter = 0;
  for( Counter = 1; Counter <= MAXADDR; Counter ++ )
   {
    HEV Ready = NULLHANDLE;

    Error_code = DosCreateEventSem( When_ready[ Counter ], &Ready, 0, 0 );
    if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

    {
     ULONG Cell_number = Counter;
     Error_code = DosCreateThread( &Threads[ Counter ], (PFNTHREAD) PingerThread, Cell_number, 0, 4096 );
     if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );
    }

    Error_code = DosWaitEventSem( Ready, EVTSEM_TIMEOUT );
    if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

    Error_code = DosCloseEventSem( Ready ); Ready = NULLHANDLE;
    if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

    {
     CHAR Cmd_exe[] = "Cmd.exe";
     CHAR Parameters[ COMMAND_LENGTH ] = "";

     strcpy( Parameters, Cmd_exe );
     strcat( Parameters, "|" );
     strcat( Parameters, "/C Ping.exe " );

     {
      CHAR Host[ NUMBER_LENGTH ] = ""; itoa( Counter, Host, 10 );

      strcat( Parameters, Network );
      strcat( Parameters, "." );
      strcat( Parameters, Host );
     }

     strcat( Parameters, " 1 1" );

     strcat( Parameters, " >" );
     strcat( Parameters, Pipe_name[ Counter ] );
     strcat( Parameters, "|" );

     {
      INT Position = 0; INT Length = strlen( Parameters );
      for( Position = 0; Position < Length; Position ++ )
       if( Parameters[ Position ] == '|' ) Parameters[ Position ] = 0;
     }

     {
      CHAR Error_string[ MESSAGE_LENGTH ] = ""; RESULTCODES Return_codes = { 0, 0 };

      Error_code = DosExecPgm( Error_string, sizeof( Error_string ), EXEC_BACKGROUND,
                               Parameters, NULL, &Return_codes, Cmd_exe );

      if( Error_code != NO_ERROR )
       {
        Err( Error_string ); Err( "\r\n" );
        Throw( Error_code, FILE, LINE );
       }
     }
    }

    {
     ULONG Wait_time = 1;

     while( 1 )
      {
       INT Threads_quantity = 0; INT Cell = 0;
       for( Cell = 0; Cell < THREADS; Cell ++ )
        if( Threads[ Cell ] ) Threads_quantity ++;

       if( Threads_quantity < THREADS / 3 ) break;

       DosSleep( Wait_time ); Wait_time *= 4;
      }
    }
   }
 }

 {
  INT Counter = 0;
  for( Counter = 1; Counter <= MAXADDR; Counter ++ )
   DosWaitThread( &Threads[ Counter ], DCWW_WAIT );
 }

 DosExit( EXIT_PROCESS, NO_ERROR );
}

/* * */

VOID APIENTRY PingerThread( ULONG Cell_number )
{
 APIRET Error_code = NO_ERROR;

 HPIPE Pipe = NULLHANDLE;
 CHAR Pipe_report[ REPORT_LENGTH ] = "";

 if( !Cell_number ) Throw( ERROR_ARENA_TRASHED, FILE, LINE );

 {
  ULONG Wait_time = 1; Error_code = -1;

  while( Error_code != NO_ERROR )
   {
    Error_code = DosCreateNPipe( Pipe_name[ Cell_number ], &Pipe,
                                 NP_ACCESS_INBOUND,
                                 NP_WAIT | 0x01,
                                 0, REPORT_LENGTH,
                                 REPORT_TIMEOUT );

    if( Error_code != NO_ERROR )
     {
      if( Error_code == ERROR_TOO_MANY_OPEN_FILES )
       {
        DosSleep( Wait_time ); Wait_time *= 4; continue;
       }
      else
       {
        Throw( Error_code, FILE, LINE );
       }
     }
   }
 }

 {
  HEV Ready = NULLHANDLE;

  Error_code = DosOpenEventSem( When_ready[ Cell_number ], &Ready );
  if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

  Error_code = DosPostEventSem( Ready );
  if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

  Error_code = DosCloseEventSem( Ready ); Ready = NULLHANDLE;
  if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );
 }

 Error_code = DosConnectNPipe( Pipe );
 if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

 {
  ULONG REPORT_LENGTHgth = 0; Pipe_report[ 0 ] = 0;

  Error_code = DosRead( Pipe, Pipe_report, REPORT_LENGTH, &REPORT_LENGTHgth );
  if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

  if( REPORT_LENGTHgth == REPORT_LENGTH ) Pipe_report[ REPORT_LENGTH - 1 ] = 0;
 }

 Error_code = DosDisConnectNPipe( Pipe );
 if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

 Error_code = DosClose( Pipe ); Pipe = NULLHANDLE;
 if( Error_code != NO_ERROR ) Throw( Error_code, FILE, LINE );

 if( strstr( Pipe_report, "icmp" ) )
  {
   PCHAR Line_begin = strstr( Pipe_report, "PING " );
   PCHAR Line_break = strstr( Line_begin, ":" );

   if( Line_begin && Line_break )
    {
     CHAR Thread[ NUMBER_LENGTH ] = "";
     CHAR Report[ NUMBER_LENGTH ] = "";

     strcat( Report, "T" );
     itoa( Cell_number, Thread, 10 );
     if( strlen( Thread ) == 1 ) strcat( Report, "00" );
     if( strlen( Thread ) == 2 ) strcat( Report, "0" );
     strcat( Report, Thread ); strcat( Report, ":icmp.ping   \t" );

     Line_begin += strlen( "PING " ); *Line_break = 0;
     strcat( Report, Line_begin );
     strcat( Report, "\r\n" );

     Say( Report );
    }

  }

 Threads[ Cell_number ] = 0;

 DosExit( EXIT_THREAD, NO_ERROR );
}
