#define FILE __FILE__
#define LINE __LINE__

VOID Throw( ULONG Error, PSZ File, ULONG Line )
{
 CHAR String[ 16 ] = "";

 Err( "\r\n" );

 itoa( Error, String, 10 );
 Err( "Error " );
 Err( String );

 Err( " in " );
 Err( File );

 itoa( Line, String, 10 );
 Err( " : " );
 Err( String );

 Err( "\r\n" );

 DosExit( EXIT_PROCESS, -1 );
}
