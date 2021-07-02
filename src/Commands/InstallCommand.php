<?php

namespace ManeOlawale\Laravel\Termii\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'termii:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs Termii assets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("<info>Setting up Termii</info>");
        $this->line("");
        sleep(2);

        $this->call('vendor:publish', [
            '--tag' => 'termii.config'
        ]);

        $this->line("");
        sleep(2);
        $this->line("<info> Termii installed sucessfully!!</info>");
    }
}
