<?php

namespace App\Console\Commands;

use App\Services\TT\ITT;
use Illuminate\Console\Command;

class CheckTaskToSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:tasks {channel=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether there are tasks of posts to send';

    /**
     * @var ITT
     */
    protected $tt;

    /**
     * Create a new command instance.
     *
     * @param ITT $ITT
     */
    public function __construct(ITT $ITT)
    {
        parent::__construct();
        $this->tt = $ITT;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channelAll = $this->argument('channel') === 'all' ? 0 : $this->argument('channel');
        $this->tt->checkChannelsPosts($channelAll);
    }
}
