<?php
use Eriodesign\Scout\Command\DeleteIndexCommand;
use Eriodesign\Scout\Command\FlushCommand;
use Eriodesign\Scout\Command\ImportCommand;
use Eriodesign\Scout\Command\IndexCommand;

return [
    IndexCommand::class,
    ImportCommand::class,
    FlushCommand::class,
    DeleteIndexCommand::class
];
