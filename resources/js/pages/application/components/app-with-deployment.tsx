import { Head, usePage } from '@inertiajs/react';
import { Site } from '@/types/site';
import ServerLayout from '@/layouts/server/layout';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { BookOpenIcon, MoreHorizontalIcon, RocketIcon } from 'lucide-react';
import React from 'react';
import { PaginatedData } from '@/types';
import { Deployment } from '@/types/deployment';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import DeploymentScript from '@/pages/application/components/deployment-script';
import Env from '@/pages/application/components/env';
import Deploy from '@/pages/application/components/deploy';
import { DataTable } from '@/components/data-table';
import { columns } from '@/pages/application/components/deployment-columns';
import AutoDeployment from '@/pages/application/components/auto-deployment';

export default function AppWithDeployment() {
  const page = usePage<{
    server: Server;
    site: Site;
    deployments: PaginatedData<Deployment>;
    deploymentScript: string;
  }>();

  return (
    <ServerLayout>
      <Head title={`${page.props.site.domain} - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Application" description="Here you can manage the deployed application" />
          <div className="flex items-center gap-2">
            <a href="https://vitodeploy.com/docs/sites/application" target="_blank">
              <Button variant="outline">
                <BookOpenIcon />
                <span className="hidden lg:block">Docs</span>
              </Button>
            </a>
            <Deploy site={page.props.site}>
              <Button>
                <RocketIcon />
                <span className="hidden lg:block">Deploy</span>
              </Button>
            </Deploy>
            <DropdownMenu modal={false}>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" className="p-0">
                  <span className="sr-only">Open menu</span>
                  <MoreHorizontalIcon />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <AutoDeployment site={page.props.site}>
                  <DropdownMenuItem onSelect={(e) => e.preventDefault()} disabled={!page.props.site.source_control_id}>
                    {page.props.site.auto_deploy ? 'Disable' : 'Enable'} auto deploy
                  </DropdownMenuItem>
                </AutoDeployment>
                <DeploymentScript site={page.props.site} script={page.props.deploymentScript}>
                  <DropdownMenuItem onSelect={(e) => e.preventDefault()}>Deployment Script</DropdownMenuItem>
                </DeploymentScript>
                <Env site={page.props.site}>
                  <DropdownMenuItem onSelect={(e) => e.preventDefault()}>Update .env</DropdownMenuItem>
                </Env>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.deployments} />
      </Container>
    </ServerLayout>
  );
}
