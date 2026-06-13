<?php

namespace Meritum\Cli;

enum ExitCode: int
{
    case Success = 0;
    case Error   = 1;
}
