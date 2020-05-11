<?php
  class Logger
  {
    const DEBUG      = 'DEBUG';
    const INFO       = 'INFO';
    const WARN       = 'WARN';
    const ERROR      = 'ERROR';
    const LOG_LEVELS = [self::DEBUG, self::INFO, self::WARN, self::ERROR];

    private $files = [];
    private $logsDir;
    
    public $stdOut = false;

    function __construct( $logsDir = LOGS_DIR, $stdOut = false )
    {
      $this->logsDir = $logsDir;
      $this->stdOut = $stdOut;
      $this->files['ALL'] = fopen( $logsDir . '/all.log', 'a+' );
    }

    function __destruct()
    {
      foreach($this->files as $file) {
        fclose($file);
      }
    }

    protected function log($message, $level, $printBacktrace = false)
    {
      $time = date('Y-m-d H:i:s');

      $level = trim($level);
      if (in_array($level, self::LOG_LEVELS)) {
        $fp = $this->files[$level] = $this->files[$level]
              ? $this->files[$level] 
              : fopen($this->logsDir . '/' . strtolower($level) . '.log', 'a+');
      }

      if ( is_array( $message ) || is_object( $message ) ) {
        $message = json_encode($message);
      }
      
      if ($printBacktrace) {
        $e = new Exception();
        $message .= "\n ---------------- \n" . 
                    $e->getTraceAsString()   . 
                    "\n ----------------";
      }

      if ($fp) { fwrite($fp, "$time $message \n"); }
      fwrite( $this->files['ALL'], "$time [$level] $message \n" );

      if ($this->stdOut) { echo "$time [$level] $message \n"; }
    }

    public function debug($message, $printBacktrace = false) { $this->log($message, self::DEBUG, $printBacktrace); }
    public function info($message,  $printBacktrace = false) { $this->log($message, self::INFO,  $printBacktrace); }
    public function warn($message,  $printBacktrace = false) { $this->log($message, self::WARN,  $printBacktrace); }
    public function error($message, $printBacktrace = false) { $this->log($message, self::ERROR, $printBacktrace); }    
  }